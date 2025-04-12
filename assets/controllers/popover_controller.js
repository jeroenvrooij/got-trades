import { Controller } from "@hotwired/stimulus"
import * as bootstrap from "bootstrap"

export default class extends Controller {
  connect() {
    this.initializeUI()

    this.observer = new MutationObserver(() => {
      this.initializeUI()
    })

    // Observing the target element for any new child elements or changes in the DOM.
    this.observer.observe(this.element, {
      childList: true,
      subtree: true,
    })
  }

  disconnect() {
    // Clean up the observer when the controller is disconnected
    if (this.observer) {
      this.observer.disconnect()
    }
  }

  initializeUI() {
    // Initialize tooltips
    const tooltipTriggers = this.element.querySelectorAll('[data-bs-toggle="tooltip"]')
    tooltipTriggers.forEach(el => {
      if (!el._tooltipInitialized) {
        try {
          new bootstrap.Tooltip(el)
          el._tooltipInitialized = true
        } catch (error) {
          console.error("Error initializing tooltip: ", error)
        }
      }
    })

    // Initialize popovers
    const popoverTriggers = this.element.querySelectorAll('[data-bs-toggle="popover"]')
    popoverTriggers.forEach(el => {
      if (!el._popoverInitialized) {
        try {
          const popover = new bootstrap.Popover(el)
          el._popoverInitialized = true
          // Add a click event listener to toggle the popover visibility
          el.addEventListener('click', (event) => {
            if (popover._isShown()) {
              popover.hide()
            } else {
              popover.show()
            }
          })
        } catch (error) {
          console.error("Error initializing popover: ", error)
        }
      }
    })
  }
}
