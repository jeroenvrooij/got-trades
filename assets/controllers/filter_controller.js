import { Controller } from "@hotwired/stimulus";

  
export default class extends Controller {
    static targets = ["form"];

    connect() {
        this.element.addEventListener("change", this.submitForm.bind(this));
    }

    submitForm() {
        this.formTarget.requestSubmit();
    }
}

