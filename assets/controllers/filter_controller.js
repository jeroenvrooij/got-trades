import { Controller } from "@hotwired/stimulus";

  
export default class extends Controller {
    static targets = ["filterForm"];

    connect() {
        this.element.addEventListener("change", this.submitForm.bind(this));
    }

    submitForm() {
        this.filterFormTarget.requestSubmit();
    }
}

