document.addEventListener('DOMContentLoaded', () => {

    const slider = document.getElementById('songSlider');
    const slideWidth = 310;

    let autoplayInterval;
    const autoplayDelay = 2000; // ms

    slider.style.transform = `translateX(-${slideWidth}px)`;

    function slideRight() {
        slider.style.transition = 'transform 0.4s ease';
        slider.style.transform = `translateX(-${slideWidth * 2}px)`;

        slider.addEventListener('transitionend', () => {
            slider.style.transition = 'none';
            slider.appendChild(slider.firstElementChild);
            slider.style.transform = `translateX(-${slideWidth}px)`;
        }, { once: true });
    }

    function startAutoplay() {
        autoplayInterval = setInterval(slideRight, autoplayDelay);
    }

    function stopAutoplay() {
        clearInterval(autoplayInterval);
    }

    // pause při hoveru
    slider.addEventListener('mouseenter', stopAutoplay);
    slider.addEventListener('mouseleave', startAutoplay);

    startAutoplay();
});
