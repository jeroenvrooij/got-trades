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
        .then(response => {
            if (response.redirected) {
                Turbo.visit(response.url); // 👈 Let Turbo handle the redirect
            }
        })
        .catch(error => console.error("Error:", error));
    }
}
