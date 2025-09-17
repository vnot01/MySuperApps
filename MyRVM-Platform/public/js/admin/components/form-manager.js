// Form Management System

class FormManager {
    constructor() {
        this.forms = new Map();
        this.validators = new Map();
    }

    registerForm(formId, options = {}) {
        const form = document.getElementById(formId);
        if (!form) {
            console.error(`Form with ID '${formId}' not found`);
            return null;
        }

        const formConfig = {
            id: formId,
            element: form,
            validators: options.validators || {},
            onSubmit: options.onSubmit || null,
            onSuccess: options.onSuccess || null,
            onError: options.onError || null,
            autoValidate: options.autoValidate !== false,
            ...options
        };

        this.forms.set(formId, formConfig);
        this.setupForm(formConfig);
        
        return formConfig;
    }

    setupForm(formConfig) {
        const { element, autoValidate, onSubmit } = formConfig;

        // Setup form submission
        element.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (autoValidate && !this.validateForm(formConfig.id)) {
                return;
            }

            if (onSubmit) {
                try {
                    await onSubmit(e, formConfig);
                } catch (error) {
                    console.error('Form submission error:', error);
                    if (formConfig.onError) {
                        formConfig.onError(error, formConfig);
                    }
                }
            }
        });

        // Setup real-time validation if enabled
        if (autoValidate) {
            const inputs = element.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.addEventListener('blur', () => {
                    this.validateField(formConfig.id, input.name);
                });
            });
        }
    }

    validateForm(formId) {
        const formConfig = this.forms.get(formId);
        if (!formConfig) return false;

        const { element, validators } = formConfig;
        let isValid = true;

        // Clear previous errors
        this.clearFormErrors(formId);

        // Validate each field
        Object.keys(validators).forEach(fieldName => {
            const field = element.querySelector(`[name="${fieldName}"]`);
            if (field && !this.validateField(formId, fieldName)) {
                isValid = false;
            }
        });

        return isValid;
    }

    validateField(formId, fieldName) {
        const formConfig = this.forms.get(formId);
        if (!formConfig) return false;

        const { element, validators } = formConfig;
        const field = element.querySelector(`[name="${fieldName}"]`);
        const fieldValidators = validators[fieldName];

        if (!field || !fieldValidators) return true;

        const value = field.value.trim();
        let isValid = true;
        let errorMessage = '';

        // Run validators
        for (const validator of fieldValidators) {
            const result = validator(value, field);
            if (result !== true) {
                isValid = false;
                errorMessage = result;
                break;
            }
        }

        // Show/hide error
        this.setFieldError(formId, fieldName, isValid ? null : errorMessage);

        return isValid;
    }

    setFieldError(formId, fieldName, errorMessage) {
        const formConfig = this.forms.get(formId);
        if (!formConfig) return;

        const { element } = formConfig;
        const field = element.querySelector(`[name="${fieldName}"]`);
        if (!field) return;

        // Remove existing error
        const existingError = field.parentNode.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }

        // Add new error if provided
        if (errorMessage) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'field-error text-danger small mt-1';
            errorDiv.textContent = errorMessage;
            field.parentNode.appendChild(errorDiv);
            field.classList.add('is-invalid');
        } else {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
        }
    }

    clearFormErrors(formId) {
        const formConfig = this.forms.get(formId);
        if (!formConfig) return;

        const { element } = formConfig;
        const errorElements = element.querySelectorAll('.field-error');
        errorElements.forEach(error => error.remove());

        const fields = element.querySelectorAll('input, select, textarea');
        fields.forEach(field => {
            field.classList.remove('is-invalid', 'is-valid');
        });
    }

    getFormData(formId) {
        const formConfig = this.forms.get(formId);
        if (!formConfig) return null;

        const { element } = formConfig;
        const formData = new FormData(element);
        const data = {};

        for (const [key, value] of formData.entries()) {
            data[key] = value;
        }

        return data;
    }

    setFormData(formId, data) {
        const formConfig = this.forms.get(formId);
        if (!formConfig) return;

        const { element } = formConfig;
        
        Object.keys(data).forEach(key => {
            const field = element.querySelector(`[name="${key}"]`);
            if (field) {
                if (field.type === 'checkbox' || field.type === 'radio') {
                    field.checked = data[key];
                } else {
                    field.value = data[key];
                }
            }
        });
    }

    resetForm(formId) {
        const formConfig = this.forms.get(formId);
        if (!formConfig) return;

        const { element } = formConfig;
        element.reset();
        this.clearFormErrors(formId);
    }

    // Common validators
    static validators = {
        required: (value) => value ? true : 'This field is required',
        email: (value) => {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(value) ? true : 'Please enter a valid email address';
        },
        minLength: (min) => (value) => 
            value.length >= min ? true : `Minimum length is ${min} characters`,
        maxLength: (max) => (value) => 
            value.length <= max ? true : `Maximum length is ${max} characters`,
        numeric: (value) => !isNaN(value) && !isNaN(parseFloat(value)) ? true : 'Please enter a valid number',
        url: (value) => {
            try {
                new URL(value);
                return true;
            } catch {
                return 'Please enter a valid URL';
            }
        }
    };
}

// Global form manager instance
const formManager = new FormManager();

// Convenience functions
function registerForm(formId, options = {}) {
    return formManager.registerForm(formId, options);
}

function validateForm(formId) {
    return formManager.validateForm(formId);
}

function getFormData(formId) {
    return formManager.getFormData(formId);
}

function setFormData(formId, data) {
    return formManager.setFormData(formId, data);
}

function resetForm(formId) {
    return formManager.resetForm(formId);
}
