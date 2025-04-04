import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    connect() {
        this.toggleVisibility = this.toggleVisibility.bind(this);
        window.addEventListener('scroll', this.toggleVisibility);
    }

    disconnect() {
        window.removeEventListener('scroll', this.toggleVisibility);
    }

    toggleVisibility() {
        const btn = this.element;
        if (window.scrollY > 300) {
            btn.style.display = 'block';
        } else {
            btn.style.display = 'none';
        }
    }

    scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }
}
