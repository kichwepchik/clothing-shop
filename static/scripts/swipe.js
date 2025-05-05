let touchstartY = 0;
let touchendY = 0;

const handleSwipe = () => {
    if (touchendY > touchstartY) {
        window.scrollBy(0, window.innerHeight);  // Прокручиваем вниз на высоту экрана
    }else if (touchendY < touchstartY) {
        window.scrollBy(0, -window.innerHeight);  // Прокручиваем вверх на высоту экрана
    }
};

document.addEventListener('touchstart', (event) => {
    touchstartY = event.changedTouches[0].screenY;
}, false);

document.addEventListener('touchend', (event) => {
    touchendY = event.changedTouches[0].screenY;
    handleSwipe();
}, false);

