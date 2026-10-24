document.getElementById("myForm").addEventListener("submit", function(event) {

    event.preventDefault();

    let name = document.getElementById("name").value;
    let email = document.getElementById("email").value;
    let password = document.getElementById("password").value;
    let confirmPassword = document.getElementById("confirmPassword").value;
    let age = document.getElementById("age").value;
    let phone = document.getElementById("phone").value;
    let course = document.getElementById("course").value;
    let gender = document.querySelector('input[name="gender"]:checked');

    if (name == "") {
        alert("Please enter your name");
        return;
    }

    if (email == "") {
        alert("Please enter your email");
        return;
    }

    if (password == "") {
        alert("Please enter your password");
        return;
    }

    if (confirmPassword == "") {
        alert("Please confirm your password");
        return;
    }

    if (password != confirmPassword) {
        alert("Passwords do not match");
        return;
    }

    if (age == "") {
        alert("Please enter your age");
        return;
    }

    if (phone == "") {
        alert("Please enter your phone number");
        return;
    }

    if (!gender) {
        alert("Please select your gender");
        return;
    }

    if (course == "") {
        alert("Please select your course");
        return;
    }

    alert("Registration successful!");
});