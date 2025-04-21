import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    static targets = ["infiniteScrollSpinner", "paginationInfoContainer", "filterForm", "renderedSetsContainer", "offset"]

    renderedSets = [];

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
        this.resetSpinner(0);
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
        let offset = 0;
        this.offsetTargets.forEach(div => {
            const latestOffset = parseInt(div.dataset.nextOffset, 10);
            if (latestOffset > offset) {
                offset = latestOffset;
            }
        })

        // Start with the existing query params from the URL
        const params = new URLSearchParams(url.search)

        // Add form values
        const formData = new FormData(this.filterFormTarget)
        for (const [key, value] of formData.entries()) {
            params.set(key, value)
        }

        this.renderedSetsContainerTargets.forEach(div => {
            if (!this.renderedSets.includes(div.dataset.renderedSets)) {
                this.renderedSets.push(div.dataset.renderedSets)
            }
        })
        params.set('offset', offset)
        params.set('renderedSet', this.renderedSets)
        url.search = params.toString()

        Turbo.visit(url.toString());

        this.resetSpinner(offset);
    }

    // 👇 Reset the flag when filters change or form submits
    resetSpinner(offset) {
        this.readyToLoad = false; // Prevent early loading *until frame finishes replacing*
        const totalResults = parseInt(this.paginationInfoContainerTarget.dataset.totalResults, 10);
        // const nextOffset = parseInt(this.paginationInfoContainerTarget.dataset.nextOffset, 10);

        if (offset >= totalResults) {
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
