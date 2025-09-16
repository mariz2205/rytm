const signupForm = document.getElementById("signupForm");
const signupMsg = document.getElementById("signupMsg");

if (signupForm) {
  signupForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const formData = new FormData(signupForm);

    try {
      const res = await fetch("../backend/signup.php", {
        method: "POST",
        body: formData,
        credentials: "include"
      });

      const data = await res.json();
      console.log("Signup response:", data);

      if (data.success) {
        signupMsg.style.color = "green";
        signupMsg.textContent = "Signup successful! Redirecting...";
        setTimeout(() => {
          window.location.href = data.redirect;
        }, 1000);
      } else {
        signupMsg.style.color = "red";
        signupMsg.textContent = data.message || "Signup failed.";
      }

    } catch (err) {
      console.error("Signup error:", err);
      signupMsg.style.color = "red";
      signupMsg.textContent = "Server error. Please try again later.";
    }
  });
}