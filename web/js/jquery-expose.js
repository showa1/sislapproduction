/**
 * jQuery Expose Shim
 * Memastikan jQuery dari vendor.bundle.base.js dapat diakses secara global
 * sebagai window.jQuery dan window.$ untuk kompatibilitas dengan yii.js dan plugin lainnya.
 */
(function() {
    // vendor.bundle.base.js berisi jQuery tetapi kadang tidak mengeksposnya ke window
    // karena kondisi UMD / module.exports. Shim ini memaksa ekspos ke window.
    if (typeof jQuery !== 'undefined' && !window.jQuery) {
        window.jQuery = jQuery;
        window.$ = jQuery;
    }
    // Jika jQuery sudah ada di window (dari bundle), pastikan $ juga terdaftar
    if (window.jQuery && !window.$) {
        window.$ = window.jQuery;
    }
})();
