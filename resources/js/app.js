import "./bootstrap";
document.addEventListener("livewire:navigated", () => {
    if (window.tallstack) {
        window.tallstack.init();
        console.log("TallStackUI reinitialized after navigation.");
    }
});
