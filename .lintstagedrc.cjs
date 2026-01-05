module.exports = {
    'resources/js/**/*.{ts,tsx}': [
        'eslint --fix',
        'prettier --write',
    ],
    'resources/js/**/*.{css,scss}': [
        'prettier --write',
    ],
    '*.{json,md}': [
        'prettier --write',
    ],
};
