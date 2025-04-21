/**
 * PDF Tab Switcher — Clipboard Interaction
 * Handles tab switching between two embedded PDF overlays in Elementor.
 *
 * @file clipboard_toggle.js
 * @author Carson Ruebel
 * @license GPL-2.0+
 * @since 1.0.0
 */

document.addEventListener("DOMContentLoaded", function () {
    const t1Btns = document.querySelectorAll(".tab-1-btn");
    const t2Btns = document.querySelectorAll(".tab-2-btn");
    const t1PDFs = document.querySelectorAll(".tab-1-pdf");
    const t2PDFs = document.querySelectorAll(".tab-2-pdf");

    /**
     * Sets up initial view based on how many PDFs are provided.
     */
    function initializeView() {
        if (t1PDFs.length && !t2PDFs.length) {
            // Only tab 1 exists
            t1PDFs.forEach(showElement);
            hideElements(t1Btns);
            hideElements(t2Btns);
        } else if (t2PDFs.length && !t1PDFs.length) {
            // Only tab 2 exists
            t2PDFs.forEach(showElement);
            hideElements(t1Btns);
            hideElements(t2Btns);
        } else if (t1PDFs.length && t2PDFs.length) {
            // Both tabs exist, default to tab 1
            t1PDFs.forEach(showElement);
            t2PDFs.forEach(hideElement);
            setTabActive(t1Btns, true);
            setTabActive(t2Btns, false);
        } else {
            console.error("❌ ERROR: No PDF elements found.");
        }
    }

    /**
     * Reloads the embedded PDFs by resetting their iframe src.
     */
    function reloadPDF(pdfElements) {
        pdfElements.forEach(pdfElement => {
            const iframe = pdfElement.querySelector("iframe");
            if (iframe) {
                const src = iframe.src;
                iframe.src = "";
                setTimeout(() => iframe.src = src, 50);
            }
        });
    }

    function showTab1() {
        t1PDFs.forEach(showElement);
        t2PDFs.forEach(hideElement);
        setTabActive(t1Btns, true);
        setTabActive(t2Btns, false);
        reloadPDF(t1PDFs);
    }

    function showTab2() {
        t2PDFs.forEach(showElement);
        t1PDFs.forEach(hideElement);
        setTabActive(t2Btns, true);
        setTabActive(t1Btns, false);
        reloadPDF(t2PDFs);
    }

    function showElement(el) {
        el.style.visibility = "visible";
        el.style.opacity = "1";
        el.style.zIndex = "2";
    }

    function hideElement(el) {
        el.style.visibility = "hidden";
        el.style.opacity = "0";
        el.style.zIndex = "1";
    }

    function hideElements(els) {
        els.forEach(el => el.style.display = "none");
    }

    function setTabActive(btns, isActive) {
        btns.forEach(btn => {
            btn.classList.toggle("active-tab", isActive);
            btn.classList.toggle("inactive-tab", !isActive);
        });
    }

    // Event listeners for tab buttons
    t1Btns.forEach(btn => btn.addEventListener("click", e => {
        e.preventDefault();
        showTab1();
    }));

    t2Btns.forEach(btn => btn.addEventListener("click", e => {
        e.preventDefault();
        showTab2();
    }));

    initializeView();
});