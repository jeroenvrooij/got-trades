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
        this.scrollToTopThenSubmitForm();
    }

    scrollToTopThenSubmitForm() {
        const form = this.filterFormTarget

        const onScroll = () => {
            if (window.scrollY === 0) {
                window.removeEventListener('scroll', onScroll)
                form.requestSubmit()
            }
        }

        // Add listener first
        window.addEventListener('scroll', onScroll)

        // Trigger scroll
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        })

        // Fallback: in case scroll event doesn't fire (e.g., already at top)
        if (window.scrollY === 0) {
            window.removeEventListener('scroll', onScroll)

            form.requestSubmit()
        }
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
