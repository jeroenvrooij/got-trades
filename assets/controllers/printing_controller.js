import { Controller } from "@hotwired/stimulus";

  
export default class extends Controller {
    static targets = ["amountInput"];

    update(event) {
        const foiling = event.target.value;
        const url = new URL(window.location.href);
        url.searchParams.set('foiling-filter', foiling);

        // Turbo will automatically fetch and update the frame
        Turbo.visit(url, { frame: "printing_table" });
    }

    incrementAmount(event) {
        const inputField = this.amountInputTarget; // Accessing the target
        let currentValue = parseInt(inputField.value);
        inputField.value = currentValue + 1;

        // Call the backend (via fetch or another method)
        this.updateAmount(event, inputField.value);
    }

    // Method to handle decrement
    decrementAmount(event) {
        const inputField = this.amountInputTarget; // Accessing the target
        let currentValue = parseInt(inputField.value);
        if (currentValue == 0) {
            return;
        }
        if (currentValue > 0) {
            inputField.value = currentValue - 1;
        }

        // Call the backend (via fetch or another method)
        this.updateAmount(event, inputField.value);
    }


    updateAmount(event, amount) {
        const id = event.params.id; 
        const setId = event.params.setId;
        const cardName = event.params.cardName;

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
                this.showToast("Success", `You now own ${cardName} ${amount} times`, "success");
            } else {
                this.showToast("Error", "Failed to update quantity!", "danger");
            }
        })
        .catch(error => {
            console.error("Error updating quantity:", error);
        });
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

