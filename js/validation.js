document.addEventListener("DOMContentLoaded", function () {

    const fieldsToValidate = document.querySelectorAll("[data-required], [pattern], input[type='email'], input[type='url'], input[type='tel'], input[type='number']");
    
    // ============================================
    // 1. バリデーションルール辞書（拡張可能！）
    // ============================================
    const validateRules = {

        // ① text / textarea / select（空チェック）
        "default": function (field) {
            return field.value.trim() !== "";
        },

        // ② email
        "email": function (field) {
            if (field.value.trim() === "") return false;
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value);
        },

        // ③ url
        "url": function (field) {
            if (field.value.trim() === "") return false;
            return /^(https?:\/\/[^\s$.?#].[^\s]*)$/i.test(field.value);
        },

        // ④ number
        "number": function (field) {
            if (field.value.trim() === "") return false;
            return !isNaN(field.value);
        },

        // ⑤ tel
        "tel": function (field) {
            if (field.value.trim() === "") return false;
            return /^[0-9\-+]+$/.test(field.value);
        },

        // ⑥ checkbox（グループ必須）
        "checkbox": function (field) {
            const group = document.querySelectorAll(`input[name="${field.name}"]`);
            return [...group].some(el => el.checked);
        },

        // ⑦ radio（グループ必須）
        "radio": function (field) {
            const group = document.querySelectorAll(`input[name="${field.name}"]`);
            return [...group].some(el => el.checked);
        }
    };

    // ============================================
    // 2. エラー表示関連（共通）
    // ============================================

    function getErrorTarget(field) {
        if (field.type === "radio" || field.type === "checkbox") {
            const group = document.querySelectorAll(`input[name="${field.name}"]`);
            return group[group.length - 1].closest("label");
        }
        return field;
    }

    function removeError(field) {
        const target = getErrorTarget(field);
        const next = target.nextElementSibling;

        if (next && next.classList.contains("error-message")) {
            next.remove();
        }
    }

    function showError(field, message) {
        removeError(field);

        const target = getErrorTarget(field);
        const p = document.createElement("p");
        p.classList.add("error-message");
        p.textContent = message;
        p.style.color = "red";
        target.insertAdjacentElement("afterend", p);
    }

    // ============================================
    // 3. フィールド単体のバリデーション
    // ============================================
    function validateField(field) {
        const type = field.type;
        const rule = validateRules[type] || validateRules["default"];
        let isValid = true;
        let errorMessage = "";

        const isRequired = field.hasAttribute('data-required');
        
        // For radio and checkbox, empty check is different
        if (type === "radio" || type === "checkbox") {
            if (isRequired && !rule(field)) {
                isValid = false;
                errorMessage = field.dataset.message || "必須項目です。";
            }
        } else {
            const isEmpty = field.value.trim() === "";
            
            if (isEmpty) {
                if (isRequired) {
                    isValid = false;
                    errorMessage = field.dataset.message || "必須項目です。";
                }
            } else {
                // Not empty, validate format
                if (!rule(field)) {
                    isValid = false;
                    errorMessage = field.dataset.message || "入力形式が正しくありません。";
                } else if (field.hasAttribute('pattern')) {
                    // Check custom pattern
                    const pattern = field.getAttribute('pattern');
                    const regex = new RegExp("^(?:" + pattern + ")$");
                    if (!regex.test(field.value)) {
                        isValid = false;
                        errorMessage = field.dataset.message || "入力形式が正しくありません。";
                    }
                }
            }
        }

        if (!isValid) {
            showError(field, errorMessage);
        } else {
            removeError(field);
        }

        return isValid;
    }

    // ============================================
    // 4. リアルタイム & blurイベント
    // ============================================

    fieldsToValidate.forEach(field => {
        field.addEventListener("blur", () => validateField(field));

        if (field.type === "radio" || field.type === "checkbox") {
            const group = document.querySelectorAll(`input[name="${field.name}"]`);
            group.forEach(el => el.addEventListener("change", () => validateField(field)));
        } else {
            field.addEventListener("input", () => validateField(field));
            field.addEventListener("change", () => validateField(field));
        }
    });

    // ============================================
    // 5. 送信 / 確認ボタンで全チェック
    // ============================================
    const submitButtons = document.querySelectorAll("button[type='submit'], input[type='submit']");

    submitButtons.forEach(button => {
        button.addEventListener("click", function (e) {
            let allValid = true;

            fieldsToValidate.forEach(field => {
                if (!validateField(field)) {
                    allValid = false;
                }
            });

            if (!allValid) {
                e.preventDefault();
                alert("入力内容にエラーがあります。ご確認ください。");
            }
        });
    });
});
