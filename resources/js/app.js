import './bootstrap'
import { register } from 'swiper/element/bundle'

/*
|--------------------------------------------------------------------------
| Global helpers
|--------------------------------------------------------------------------
*/

window.showAlert = function (message, timeout = 5000) {
    const alertContainer = document.createElement('div')
    alertContainer.className = 'alert-container'

    const alertBox = document.createElement('div')
    alertBox.className = 'alert-box'

    const alertMessage = document.createElement('p')
    alertMessage.className = 'alert-message'
    alertMessage.textContent = message

    alertBox.appendChild(alertMessage)
    alertContainer.appendChild(alertBox)
    document.body.appendChild(alertContainer)

    setTimeout(() => {
        alertContainer.remove()
    }, timeout)
}

window.scrollToElementIfNotVisible = async function (el, parent = null) {
    if (!el) return

    const rect = el.getBoundingClientRect()

    const parentRect = parent
        ? parent.getBoundingClientRect()
        : {
            top: 0,
            left: 0,
            bottom: window.innerHeight,
            right: window.innerWidth,
        }

    const isVisible =
        rect.top >= parentRect.top &&
        rect.left >= parentRect.left &&
        rect.bottom <= parentRect.bottom &&
        rect.right <= parentRect.right

    if (!isVisible) {
        el.scrollIntoView({ behavior: 'smooth', block: 'center' })
    }
}

/*
|--------------------------------------------------------------------------
| Alpine (Filament-safe)
|--------------------------------------------------------------------------
| IMPORTANT:
| - Do NOT import Alpine
| - Do NOT call Alpine.start()
| - Wait for Filament to boot Alpine
*/

document.addEventListener('alpine:init', () => {
    Alpine.data('profileContainer', () => ({
        current: 'profile',

        onScroll(event) {
            const sections = this.$refs.sections?.children || []
            const nav = this.$refs.nav?.children || []

            for (let i = 0; i < sections.length; i++) {
                const section = sections[i]
                const sectionTop = section.offsetTop

                if (event.target.scrollTop <= sectionTop) {
                    this.current = section.id
                    break
                }
            }

            for (let i = 0; i < nav.length; i++) {
                const link = nav[i].children[0]
                if (!link) continue

                if (link.getAttribute('href') === '#' + this.current) {
                    link.classList.add('active')
                } else {
                    link.classList.remove('active')
                }
            }
        },

        scrollGalleryLeft() {
            this.$refs.gallery?.scrollBy({ left: -300, behavior: 'smooth' })
        },

        scrollGalleryRight() {
            this.$refs.gallery?.scrollBy({ left: 300, behavior: 'smooth' })
        },

        scrollProjectsLeft() {
            this.$refs.projects?.scrollBy({ left: -300, behavior: 'smooth' })
        },

        scrollProjectsRight() {
            this.$refs.projects?.scrollBy({ left: 300, behavior: 'smooth' })
        },
    }))
})

/*
|--------------------------------------------------------------------------
| Swiper Web Components
|--------------------------------------------------------------------------
*/
register()

/*
|--------------------------------------------------------------------------
| UI Enhancements
|--------------------------------------------------------------------------
*/

document.addEventListener('DOMContentLoaded', () => {
    const main = document.querySelector('main')
    const topbar = document.querySelector('.fi-topbar')

    if (!main || !topbar) return

    main.addEventListener('scroll', (event) => {
        if (event.target.scrollTop > 80) {
            topbar.classList.add('nav-white-bg')
        } else {
            topbar.classList.remove('nav-white-bg')
        }
    })
})
