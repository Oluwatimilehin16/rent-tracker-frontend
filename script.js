document.addEventListener("DOMContentLoaded", function () {
    const loginForm = document.getElementById("loginForm");
    const registerForm = document.getElementById("registerForm");

    if (loginForm) {
        loginForm.addEventListener("submit", function (event) {
            const email = loginForm.email.value.trim();
            const password = loginForm.password.value.trim();

            if (email === "" || password === "") {
                alert("Please fill in all fields.");
                event.preventDefault();
            }
        });
    }

    if (registerForm) {
        registerForm.addEventListener("submit", function (event) {
            const name = registerForm.name.value.trim();
            const email = registerForm.email.value.trim();
            const password = registerForm.password.value.trim();
            const role = registerForm.role.value;

            if (name === "" || email === "" || password === "" || role === "") {
                alert("Please fill in all fields.");
                event.preventDefault();
            }
        });
    }
});
