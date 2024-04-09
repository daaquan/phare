// app.js

// prevent double submit
// https://stackoverflow.com/questions/2830542/prevent-double-submission-of-forms-in-jquery
document.querySelectorAll('form').forEach(form => {
  form.addEventListener('submit', event => {
    const submitButton = form.querySelector('[type="submit"]');
    if (submitButton) {
      submitButton.disabled = true;
    }
  });
});

// prevent resubmit on back button
// https://stackoverflow.com/questions/45656405/browser-prevent-post-data-resubmit-on-refresh-using-only-javascript
if (window.history.replaceState) {
  window.history.replaceState(null, null, window.location.href);
}

// disable back button
// https://stackoverflow.com/questions/12381563/how-to-stop-browser-back-button-using-javascript
history.pushState(null, document.title, location.href);
window.addEventListener('popstate', function (event) {
  history.pushState(null, document.title, location.href);
});

// prevent closing window
// https://stackoverflow.com/questions/821011/prevent-a-webpage-from-navigating-away-using-javascript
//window.onbeforeunload = function() { return "Your work will be lost."; };
