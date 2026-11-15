// js/validation.js - client-side validation using the SAME patterns as PHP (RULES comes from footer.php).
// Usage: <form data-validate> and <input data-rule="name">. Error text goes in the <span class="err"> next to it.
(function () {
  function check(input) {
    var rule = input.dataset.rule;
    var value = (rule === 'password' || input.type === 'password') ? input.value : input.value.trim();
    var msg = '';
    if (rule === 'required') {
      if (value === '') msg = 'This field is required.';
    } else if (RULES[rule] && !new RegExp(RULES[rule].pattern).test(value)) {
      msg = value === '' ? 'This field is required.' : RULES[rule].message;
    }
    if (!msg && input.dataset.match) {
      var other = document.getElementById(input.dataset.match);
      if (other && other.value !== input.value) msg = 'Passwords do not match.';
    }
    var err = input.parentElement.querySelector('.err');
    if (err) err.textContent = msg;
    input.classList.toggle('invalid', msg !== '');
    return msg === '';
  }

  document.querySelectorAll('form[data-validate]').forEach(function (form) {
    var inputs = form.querySelectorAll('[data-rule]');
    inputs.forEach(function (i) { i.addEventListener('blur', function () { check(i); }); });
    form.addEventListener('submit', function (ev) {
      var ok = true;
      inputs.forEach(function (i) { if (!check(i)) ok = false; });
      if (!ok) ev.preventDefault();   // stop submission if any field is invalid
    });
  });

  var t = document.getElementById('navToggle');
  if (t) t.addEventListener('click', function () { document.getElementById('navLinks').classList.toggle('open'); });
})();
