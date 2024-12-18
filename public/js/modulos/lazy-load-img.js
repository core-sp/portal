export default function (root = null, rootMargin = '0px', threshold = 0, elemento = ".lazy-loaded-image.lazy"){

    let lazyImages = Array.from(document.querySelectorAll(elemento));

    let lazyImageObserver = new IntersectionObserver((entries) => {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                let lazyImage = entry.target;

                lazyImage.src = lazyImage.dataset.src;
                lazyImage.classList.remove("lazy");
                lazyImageObserver.unobserve(lazyImage);
            }
        });
    }, {
        root: root,
        rootMargin: rootMargin,
        threshold: threshold
    });

    lazyImages.forEach(function(lazyImage) {
        lazyImageObserver.observe(lazyImage);
    });

    if(lazyImages.length == 0)
        lazyImageObserver.disconnect();
}
