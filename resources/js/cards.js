Nova.booting((Vue, router, store) => {
    Vue.component('quick-works-card', require('./components/QuickWorksCard').default),
    Vue.component('zip-update-projects-card', require('./components/ZipUpdateProjectsCard').default),
    Vue.component('slideshow-artist-card', require('./components/SlideshowArtistCard').default),
    Vue.component('edit-slideshow-card', require('./components/EditSlideshowCard').default)
})
