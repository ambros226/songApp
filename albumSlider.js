document.addEventListener('DOMContentLoaded', () => {

    const slider = document.getElementById('albumSlider');
    const slideWidth = 430; // 410 + gap

    let autoplayInterval;
    const autoplayDelay = 3000; // ms

    slider.style.transform = `translateX(-${slideWidth}px)`;

    function slideRightAlbum() {
        slider.style.transition = 'transform 0.4s ease';
        slider.style.transform = `translateX(-${slideWidth * 2}px)`;

        slider.addEventListener('transitionend', () => {
            slider.style.transition = 'none';
            slider.appendChild(slider.firstElementChild);
            slider.style.transform = `translateX(-${slideWidth}px)`;
        }, { once: true });
    }

    window.slideRightAlbum = slideRightAlbum;

    window.slideLeftAlbum = () => {
        slider.style.transition = 'none';
        slider.insertBefore(slider.lastElementChild, slider.firstElementChild);
        slider.style.transform = `translateX(-${slideWidth * 2}px)`;

        requestAnimationFrame(() => {
            slider.style.transition = 'transform 0.4s ease';
            slider.style.transform = `translateX(-${slideWidth}px)`;
        });
    };

    function startAutoplay() {
        autoplayInterval = setInterval(slideRightAlbum, autoplayDelay);
    }

    function stopAutoplay() {
        clearInterval(autoplayInterval);
    }

    // pause při hoveru
    slider.addEventListener('mouseenter', stopAutoplay);
    slider.addEventListener('mouseleave', startAutoplay);

    startAutoplay();
});
