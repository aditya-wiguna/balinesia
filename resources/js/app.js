import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    const openIcon = mobileMenuBtn?.querySelector('.menu-open-icon');
    const closeIcon = mobileMenuBtn?.querySelector('.menu-close-icon');

    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', () => {
            const isOpen = !mobileMenu.classList.toggle('hidden');
            openIcon?.classList.toggle('hidden', isOpen);
            closeIcon?.classList.toggle('hidden', !isOpen);
            mobileMenuBtn.setAttribute('aria-expanded', isOpen);
        });
    }
});
