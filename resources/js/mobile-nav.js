function mobileCheck() {
    // Use matchMedia to detect mobile viewport sizes
    const isMobile = window.matchMedia('(max-width: 767px)').matches;


    if (isMobile) {
        localStorage.setItem("isOpen", false);
    }

    return isMobile;
}

// Run the check
mobileCheck();
