Nova.booting((Vue, router, store) => {
    Vue.component('quick-works-card', require('./components/QuickWorksCard').default),
    Vue.component('slideshow-artist-card', require('./components/SlideshowArtistCard').default)
})
