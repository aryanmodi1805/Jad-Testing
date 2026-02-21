import focus from '@alpinejs/focus'
import collapse from '@alpinejs/collapse'

document.addEventListener('alpine:init', () => {
    if (window.Alpine) {
        window.Alpine.plugin(focus)
        window.Alpine.plugin(collapse)
    }
})
