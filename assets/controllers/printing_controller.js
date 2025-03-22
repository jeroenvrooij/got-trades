import { Controller } from "@hotwired/stimulus";

  
export default class extends Controller {
    static targets = ["amountInput", "foilFilter"];

    foilingFilter(event) {
        const foiling = event.target.value;
        const url = new URL(window.location.href);
        url.searchParams.set('foiling-filter', foiling);

        // Turbo will automatically fetch and update the frame
        Turbo.visit(url, { frame: "printing_table" });
    }

    // Store the timer to manage debounce
    timeoutId = null;
    
    connect() {
        this.amountInputTargets.forEach(input => {
          // Store the original value of each input
          input.dataset.originalValue = input.value;
        });
    }

    incrementAmount(event) {
        // const inputField = event.target.closest('.input-group').querySelector('input');
        const inputField = this.amountInputTarget;
        
        let currentValue = parseInt(inputField.value);
        inputField.value = currentValue + 1;

        // Debounce the request: clear any existing timer and set a new one
        this.debounceUpdate(event, inputField);
    }

    // Method to handle decrement
    decrementAmount(event) {
        const inputField = this.amountInputTarget;

        let currentValue = parseInt(inputField.value);
        if (currentValue == 0) {
            return;
        }
        if (currentValue > 0) {
            inputField.value = currentValue - 1;
        }

        // Debounce the request: clear any existing timer and set a new one
        this.debounceUpdate(event, inputField);
    }

    // Method to handle the debounced update
    debounceUpdate(event, inputField) {
        // If the value hasn't changed, do nothing
        if (inputField.value === this.originalValue) {
            return;
        }

        // Disable the select element while waiting
        this.disableFilters();   

        // If there is a pending request, clear it
        clearTimeout(this.timeoutId);

        // Set a new timer for 2 seconds
        this.timeoutId = setTimeout(() => {
            this.updateAmount(event, inputField);
        }, 2000); 
    }

    updateAmount(event, inputField) {
        const id = event.params.id; 
        const setId = event.params.setId;
        const cardName = event.params.cardName;
        const amount = inputField.value;
        
        // console.log('original value: ', parseInt(inputField.dataset.originalValue));
        // console.log('new value: ', parseInt(amount));
        // If the quantity is the same as the original, don't make the request
        if (parseInt(amount) === parseInt(inputField.dataset.originalValue)) {
            this.enableFilters();
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
            this.enableFilters(); // Re-enable the select element after the request
        });
    }
    
    disableFilters() {
        const select = document.querySelector('select[name="foiling-filter"]'); // Adjust selector
        if (select) {
          select.disabled = true;
        }
      }
    
    enableFilters() {
        const select = document.querySelector('select[name="foiling-filter"]'); // Adjust selector
        if (select) {
          select.disabled = false;
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

