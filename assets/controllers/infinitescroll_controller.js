import { Controller } from "@hotwired/stimulus"

function debounce(fn, delay) {
    let timeoutId;
    return function (...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn.apply(this, args), delay);
    };
}

export default class extends Controller {
    static targets = ["infiniteScrollSpinner", "paginationInfoContainer", "filterForm", "renderedSetsContainer", "offset"]

    renderedSets = [];

    connect() {
        this.readyToLoad = true; // 👈 Flag to prevent unwanted triggering

        if (!this.hasInfiniteScrollSpinnerTarget) return

        this.debouncedScrollCheck = debounce(this.checkScrollPosition.bind(this), 50)
        window.addEventListener('scroll', this.debouncedScrollCheck)
    }
    disconnect() {
        window.removeEventListener('scroll', this.debouncedScrollCheck)
    }
    paginationInfoContainerTargetConnected(element) {
        this.resetSpinner();
    }

    checkScrollPosition() {
        if (!this.readyToLoad) return;

        const scrollPosition = window.scrollY + window.innerHeight;
        const pageHeight = document.documentElement.scrollHeight;
        const threshold = 300;

        if (scrollPosition + threshold >= pageHeight) {
            this.loadMore();
        }
    }

    loadMore() {
        // Prevent further loading until current request completes
        this.readyToLoad = false;

        const url = new URL(this.paginationInfoContainerTarget.dataset.fetchMoreUrl)
        const offset = this.fetchLatestOffset();

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

        this.resetSpinner();
    }

    // 👇 Reset the flag when filters change or form submits
    resetSpinner(offset = null) {
        this.readyToLoad = false; // Prevent early loading *until frame finishes replacing*
        const totalResults = parseInt(this.paginationInfoContainerTarget.dataset.totalResults, 10);
        if (offset === null) {
            offset = this.fetchLatestOffset();
        }
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

    fetchLatestOffset()
    {
        let offset = 0;
        this.offsetTargets.forEach(div => {
            const latestOffset = parseInt(div.dataset.nextOffset, 10);
            if (latestOffset > offset) {
                offset = latestOffset;
            }
        })

        return offset;
    }
}
