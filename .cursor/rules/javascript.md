---
description: JavaScript coding standards
globs: ["**/*.js"]
---

# JavaScript Standards

## Modern Syntax — Mandatory

This codebase is **ES6+ only**. All new and modified JS must use modern syntax.
Legacy `var` / `function` expressions / loose equality are not acceptable, even
inside IIFEs or `view.js` files for blocks.

### Variable Declarations

- Use `const` by default. Use `let` only when the binding is reassigned.
- **Never use `var`.**

```javascript
// BAD
var nameInput = form.querySelector( '[name="name"]' );
var i;
for ( i = 0; i < items.length; i++ ) { /* ... */ }

// GOOD
const nameInput = form.querySelector( '[name="name"]' );
items.forEach( ( item ) => { /* ... */ } );

// GOOD — let only when reassigned
let firstInvalid = null;
firstInvalid = firstInvalid || input;
```

### Functions

- Arrow functions for callbacks, handlers, and short helpers.
- Top-level IIFEs use the arrow form: `( () => { /* ... */ } )();`
- Use named `function` declarations only when hoisting or `this` binding requires it.

```javascript
// BAD
( function () {
    function setFieldInvalid( input, invalid ) { /* ... */ }
    document.addEventListener( 'DOMContentLoaded', function () { /* ... */ } );
} )();

// GOOD
( () => {
    const setFieldInvalid = ( input, invalid ) => { /* ... */ };
    document.addEventListener( 'DOMContentLoaded', () => { /* ... */ } );
} )();
```

### Modern Idioms

- Strict equality only (`===` / `!==`). Never `==` / `!=`.
- Optional chaining (`?.`) and nullish coalescing (`??`) over manual null checks.
- Template literals over string concatenation.
- Destructuring over property access chains where it improves readability.

```javascript
// BAD
var field = input.closest( '.field' );
if ( field ) { field.classList.add( 'is-invalid' ); }
var label = 'Hello, ' + user.name + '!';

// GOOD
input.closest( '.field' )?.classList.add( 'is-invalid' );
const label = `Hello, ${ user.name }!`;
```

## Naming

- Variables/functions: `camelCase`
- Constants (true compile-time constants): `UPPER_SNAKE_CASE`
- Classes: `PascalCase`
- Files: `kebab-case.js`

## DOM

- Use `querySelector` / `querySelectorAll` over jQuery.
- Wrap in `DOMContentLoaded` or guard with element existence checks.
- For Gutenberg blocks, use `view.js` with vanilla JS or the Interactivity API.

## WordPress AJAX

- Use `fetch()` with `FormData`, not jQuery AJAX.
- Always include the nonce via `wp_localize_script()` from PHP.
- Wrap async operations in `try`/`catch`. Never swallow errors silently.

## Formatting

- Always use semicolons.
- Single responsibility per function.
