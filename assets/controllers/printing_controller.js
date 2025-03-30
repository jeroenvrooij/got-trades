import { Controller } from "@hotwired/stimulus";

  
export default class extends Controller {
    static targets = ["amountInput", "decrementButton", "incrementButton", "playsetIconsContainer", 'filterForm'];

    // Store array of timeout, each card will have their own
    timeouts = {}; 

    connect() {
        this.amountInputTargets.forEach(input => {
          // Store the original value of each input
          input.dataset.originalValue = input.value;
        });

        if (!window.requests) {
            window.requests = [];
        }
    }

    incrementAmount(event) {
        const inputGroup = event.target.closest(".input-group");

        // Find the input element within this container
        const inputField = inputGroup.querySelector('[data-printing-target="amountInput"]');
    
        let currentValue = parseInt(inputField.value);
        inputField.value = currentValue + 1;
        this.updateButtonState(event);

        // Debounce the request: clear any existing timer and set a new one
        this.debounceUpdate(event, inputField);
    }

    // Method to handle decrement
    decrementAmount(event) {
        // Find the input element within this container
        const inputGroup = event.target.closest(".input-group");
        const inputField = inputGroup.querySelector('[data-printing-target="amountInput"]');

        let currentValue = parseInt(inputField.value);
        if (currentValue == 0) {
            return;
        }
        if (currentValue > 0) {
            inputField.value = currentValue - 1;
        }
        this.updateButtonState(event);
        
        // Debounce the request: clear any existing timer and set a new one
        this.debounceUpdate(event, inputField);
    }
    
    updateButtonState(event) {
        const inputGroup = event.target.closest(".input-group");

        // Find the input element within this container
        const inputField = inputGroup.querySelector('[data-printing-target="amountInput"]');
        const decrementField = inputGroup.querySelector('[data-printing-target="decrementButton"]');
        const incrementField = inputGroup.querySelector('[data-printing-target="incrementButton"]');

        let value = parseInt(inputField.value, 10);
        
        // Disable decrement if value is 0
        decrementField.disabled = value <= 0;

        // OPTIONAL: Disable increment at a max limit (e.g., 10)
        incrementField.disabled = value >= 99;
      }

    // Method to handle the debounced update
    debounceUpdate(event, inputField) {
        const quantityId = event.target.closest('.input-group').dataset.quantityId; // Unique id

        // If the value hasn't changed, do nothing, remove request from 'queue' and enable filter if possible
        if (inputField.value === inputField.dataset.originalValue) {
            window.requests.splice(window.requests.indexOf(event.params.id), 1);
            this.updateFilterState();
            return;
        }

        // add the request to a 'queue' so we can only enable the filters if all requests are processed
        if (!window.requests.includes(event.params.id)) {
            window.requests.push(event.params.id);
        }

        // Disable the select element while waiting
        this.updateFilterState();   

        // If there is a pending request for this unique quantityId, clear it
        if (this.timeouts[quantityId]) {
            clearTimeout(this.timeouts[quantityId]);
        }

        // Set a new timer for 1 second, for this unique quantityId
        this.timeouts[quantityId] = setTimeout(() => {
            this.updateAmount(event, inputField);
        }, 1000);
    }

    updateAmount(event, inputField) {
        const id = event.params.id; 
        const setId = event.params.setId;
        const cardName = event.params.cardName;
        const amount = inputField.value;
        
        // If the quantity is the same as the original, don't make the request
        if (parseInt(amount) === parseInt(inputField.dataset.originalValue)) {
            return;
        }

        fetch(`/update-user-collection`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({ id, amount, setId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the original value after a successful request
                inputField.dataset.originalValue = amount;
                
                this.showToast("Success", `You now own ${cardName} ${amount} times`, "success");
            } else {
                this.showToast("Error", "Failed to update quantity!", "danger");
            }
        })
        .catch(error => {
            console.error("Error updating quantity:", error);
        })
        .finally(() => {
            // this request was handled, so remove it from the pending requests
            window.requests.splice(window.requests.indexOf(id), 1);

            // update the playset icons and re-enable the filters
            this.updatePlaysetIcons(event, amount);
            this.updateFilterState();
            console.log(window.requests);
            if (window.requests.length === 0) {
                // there are no more pending requests
                this.filterFormTarget.requestSubmit();
            }
        });
    }

    updatePlaysetIcons(event, amount) {
        const inputGroup = event.target.closest("tr"); 
        const playsetIconsContainer = inputGroup.querySelector('[data-printing-target="playsetIconsContainer"]');
        
        playsetIconsContainer.querySelectorAll(".card-icon").forEach((playsetIcon, index) => {
            playsetIcon.classList.remove("filled");
            if (index == 0 && amount > 0) {
                playsetIcon.classList.add("filled");
            }
            if (index == 1 && amount > 1) {
                playsetIcon.classList.add("filled");
            }
            if (index == 2 && amount > 2) {
                playsetIcon.classList.add("filled");
            }
        });
    }

    updateFilterState() {
        // if the request 'queue' is empty: enable filters, otherwise disable them
        const foilingFilter = document.querySelector('select[name="card_filter_form[foiling]"]'); // Adjust selector
        if (foilingFilter) {
            foilingFilter.disabled = window.requests.length > 0;
        }
        const playsetSwitch = document.querySelector('input[name="card_filter_form[hide]"]'); // Adjust selector
        if (playsetSwitch) {
            playsetSwitch.disabled = window.requests.length > 0;
        }
        const cardNameFilter = document.querySelector('input[name="card_filter_form[cardName]"]'); // Adjust selector
        if (cardNameFilter) {
            cardNameFilter.disabled = window.requests.length > 0;
        }
    }

    showToast(title, message, type = "info") {
        const toastContainer = document.querySelector("#toast-container");

        const toastId = `toast-${Date.now()}`;
        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-${type}-emphasis bg-${type}-subtle border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

        toastContainer.insertAdjacentHTML("beforeend", toastHtml);
        
        // Initialize Bootstrap toast
        const toastElement = document.getElementById(toastId);
        const toast = bootstrap.Toast.getOrCreateInstance(toastElement, { delay: 3000 }); // Auto-hide after 3s
        toast.show();

        // Remove toast after it's hidden
        toastElement.addEventListener("hidden.bs.toast", () => {
            toastElement.remove();
        });
    }
}

