// app.js

// prevent double submit
document.querySelectorAll('form').forEach(form => {
  form.addEventListener('submit', event => {
    const submitButton = form.querySelector('[type="submit"]');
    if (submitButton) {
      submitButton.disabled = true;
    }
  });
});

// prevent resubmit on back button
if (window.history.replaceState) {
  window.history.replaceState(null, null, window.location.href);
}
