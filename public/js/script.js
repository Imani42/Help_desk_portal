function showRegister() {
    document.getElementById("registerOptions").style.display = "block";
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.comments-scroll').forEach(function (scrollBox) {
        scrollBox.scrollTop = scrollBox.scrollHeight;
    });
});