import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["filterForm"];

    connect() {
        // Debounced form submission function
        this.debouncedSubmit = this.debounce(this.submitForm.bind(this), 500);

        // Attach event listeners to all form fields
        this.element.querySelectorAll("input, select, textarea").forEach((field) => {
            if (field.type === "text") {
                field.addEventListener("input", this.debouncedSubmit);
            } else {
                field.addEventListener("change", this.submitForm.bind(this));
            }
        });
    }

    submitForm() {
        this.filterFormTarget.requestSubmit();
    }

    debounce(func, delay) {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                func(...args);
            }, delay);
        };
    }
}
