import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    update(event) {
        const foiling = event.target.value;
        const url = new URL(window.location.href);
        url.searchParams.set('foiling-filter', foiling);

        // Turbo will automatically fetch and update the frame
        Turbo.visit(url, { frame: "printing_table" });
    }

    updateAmount(event) {
        const amount = event.target.value;
        const id = event.params.id; 
        const setId = event.params.setId;

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
            this.refreshFlashMessages();
        })
        .catch(error => console.error("Error:", error));
    }
    
    refreshFlashMessages() {
        fetch('/flash-messages')
            .then(response => response.text())
            .then(html => {
                const flashContainer = document.querySelector("#flash-messages");

                // Create a temporary div to extract new messages
                const tempDiv = document.createElement("div");
                tempDiv.innerHTML = html;

                // Append new messages without removing existing ones
                tempDiv.querySelectorAll(".flash-message").forEach(newMessage => {
                    flashContainer.appendChild(newMessage);

                    // Apply fade-in effect
                    setTimeout(() => {
                        newMessage.classList.add("show");
                    }, 50);

                    // Auto-hide after 3 seconds
                    setTimeout(() => {
                        newMessage.classList.add("fade-out");
                        setTimeout(() => {
                            newMessage.remove();
                        }, 500);
                    }, 3000);
                });
            });
    }
}
