import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    static targets = ["infiniteScrollSpinner", "paginationInfoContainer", "filterForm"]

    connect() {
        this.readyToLoad = true; // 👈 Flag to prevent unwanted triggering

        if (!this.hasInfiniteScrollSpinnerTarget) return

        this.observer = new IntersectionObserver(entries => {
            if (this.readyToLoad && entries[0].isIntersecting) {
                this.loadMore()
            }
        })
        this.observer.observe(this.infiniteScrollSpinnerTarget)
    }
    paginationInfoContainerTargetConnected(element) {
        this.resetSpinner();
    }
    disconnect() {
        if (this.observer) {
            this.observer.disconnect()
        }
    }

    loadMore() {
        // Prevent further loading until current request completes
        this.readyToLoad = false;

        const url = new URL(this.paginationInfoContainerTarget.dataset.fetchMoreUrl)
        const offset = parseInt(this.paginationInfoContainerTarget.dataset.nextOffset, 10)

        const formData = new FormData(this.filterFormTarget)
        const params = new URLSearchParams(formData)

        params.set('offset', offset)
        url.search = params.toString()

        const nextOffset = offset + 20;
        this.paginationInfoContainerTarget.dataset.nextOffset = nextOffset;

        Turbo.visit(url.toString());

        this.resetSpinner();
    }

    // 👇 Reset the flag when filters change or form submits
    resetSpinner() {
        this.readyToLoad = false; // Prevent early loading *until frame finishes replacing*
        const totalResults = parseInt(this.paginationInfoContainerTarget.dataset.totalResults, 10);
        const nextOffset = parseInt(this.paginationInfoContainerTarget.dataset.nextOffset, 10);

        if (nextOffset >= totalResults) {
            this.infiniteScrollSpinnerTarget.style.display = 'none'; // Hide spinner
        } else {
            this.infiniteScrollSpinnerTarget.style.display = 'block';
        }

        // 👇 Wait a bit (or hook into Turbo events) before re-enabling
        setTimeout(() => {
            this.readyToLoad = true;
        }, 500); // Adjust if needed
    }
}
