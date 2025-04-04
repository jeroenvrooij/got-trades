import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["filterForm", "foilingFilter", "collectorViewFilter"];

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
        let foilingFilterDiv = this.foilingFilterTarget.closest(".foiling-filter");
        if (this.collectorViewFilterTarget.checked || this.collectorViewFilterTarget.hidden == true) {
            foilingFilterDiv.hidden = false;
        } else {
            this.foilingFilterTarget.selectedIndex = 0;
            foilingFilterDiv.hidden = true;
        }
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
