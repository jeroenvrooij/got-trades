import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    connect() {
        console.log("✅ Filter controller connected!");
    }
    
    update(event) {
        console.log("Dropdown changed:", event.target.value); // Debugging log

        const foiling = event.target.value;
        const url = new URL(window.location.href);
        url.searchParams.set('foiling', foiling);

        // Turbo will automatically fetch and update the frame
        Turbo.visit(url, { frame: "records_table" });
    }
}
