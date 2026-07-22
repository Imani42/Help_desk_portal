function showRegister() {
    document.getElementById("registerOptions").style.display = "block";
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-district-select]').forEach(function (districtSelect) {
        var form = districtSelect.closest('form') || document;
        var regionSelect = form.querySelector('[data-region-select]');
        var fixedRegion = districtSelect.dataset.fixedRegion || '';
        var selectedDistrict = districtSelect.dataset.selectedDistrict || '';
        var locations = window.TANZANIA_LOCATIONS || {};

        function activeRegion() {
            return fixedRegion || (regionSelect ? regionSelect.value : '');
        }

        function populateDistricts() {
            var region = activeRegion();
            var districts = locations[region] || [];
            var currentValue = districtSelect.value || selectedDistrict;

            districtSelect.innerHTML = '<option value="">Select District</option>';

            districts.forEach(function (district) {
                var option = document.createElement('option');
                option.value = district;
                option.textContent = district;
                option.selected = district === currentValue;
                districtSelect.appendChild(option);
            });

            districtSelect.disabled = districts.length === 0;
        }

        if (regionSelect) {
            regionSelect.addEventListener('change', function () {
                selectedDistrict = '';
                districtSelect.value = '';
                populateDistricts();
            });
        }

        populateDistricts();
    });

    document.querySelectorAll('.comments-scroll').forEach(function (scrollBox) {
        scrollBox.scrollTop = scrollBox.scrollHeight;
    });

    document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            var field = button.closest('.password-field');
            if (!field) return;

            var input = field.querySelector('input');
            if (!input) return;

            var isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            button.classList.toggle('is-visible', isHidden);
            button.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
        });
    });

    document.querySelectorAll('.password-policy').forEach(function (policy) {
        var form = policy.closest('form');
        if (!form) return;

        var password = form.querySelector('input[name="password"]');
        if (!password) return;

        var submitButtons = form.querySelectorAll('button[type="submit"], button:not([type])');
        var rules = [
            {
                key: 'length',
                text: 'At least 8 characters',
                violation: 'Violation: password must have at least 8 characters',
                test: function (value) { return value.length >= 8; }
            },
            {
                key: 'capital',
                text: 'At least 1 capital letter',
                violation: 'Violation: add at least 1 capital letter',
                test: function (value) { return /[A-Z]/.test(value); }
            },
            {
                key: 'digits',
                text: 'At least 2 digits',
                violation: 'Violation: add at least 2 digits',
                test: function (value) { return (value.match(/\d/g) || []).length >= 2; }
            },
            {
                key: 'special',
                text: 'At least 1 special character',
                violation: 'Violation: add at least 1 special character',
                test: function (value) { return /[^A-Za-z0-9]/.test(value); }
            }
        ];

        policy.innerHTML = rules.map(function (rule) {
            return '<span class="password-rule is-neutral" data-password-rule="' + rule.key + '">' + rule.text + '</span>';
        }).join('');

        function updatePolicy() {
            var value = password.value;
            var isEmpty = value.length === 0;
            var allValid = true;

            rules.forEach(function (rule) {
                var item = policy.querySelector('[data-password-rule="' + rule.key + '"]');
                var isValid = rule.test(value);

                if (!isValid) allValid = false;
                if (!item) return;

                item.classList.toggle('is-neutral', isEmpty || !isValid);
                item.classList.toggle('is-valid', !isEmpty && isValid);
                item.classList.remove('is-invalid');
                item.textContent = rule.text;
            });

            return allValid;
        }

        password.addEventListener('input', updatePolicy);
        form.addEventListener('submit', function (event) {
            var allValid = updatePolicy();

            if (!allValid) {
                event.preventDefault();

                rules.forEach(function (rule) {
                    var item = policy.querySelector('[data-password-rule="' + rule.key + '"]');
                    if (!item || rule.test(password.value)) return;

                    item.classList.remove('is-neutral');
                    item.classList.add('is-invalid');
                    item.textContent = rule.violation;
                });

                password.focus();
            }
        });
        updatePolicy();
    });
});
