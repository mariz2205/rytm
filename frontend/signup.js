

//Handle SIGNUP form via AJAX
const signupForm = document.getElementById("signupForm");
if (signupForm) {
  signupForm.addEventListener("submit", (e) => {
    e.preventDefault();

    const formData = new FormData(signupForm);

    fetch("../backend/signup.php", {
      method: "POST",
      body: formData,
      credentials: "include" 
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          window.location.href = data.redirect;
        } else {
          document.getElementById("signupMsg").textContent = data.message;
        }
      })
      .catch(err => console.error("Signup error:", err));
  });
}
