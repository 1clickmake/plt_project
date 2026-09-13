<?php

return function(FastRoute\RouteCollector $r) {
    // Install Routes
    $r->addRoute(['GET', 'POST'], '/install', ['App\Controllers\InstallController', 'index']);
    
    // Auth Routes
    $r->addRoute('GET', '/login', ['App\Controllers\AuthController', 'showLogin']);
    $r->addRoute('POST', '/login', ['App\Controllers\AuthController', 'login']);
    $r->addRoute('GET', '/register', ['App\Controllers\AuthController', 'showRegister']);
    $r->addRoute('POST', '/register', ['App\Controllers\AuthController', 'register']);
    $r->addRoute('GET', '/logout', ['App\Controllers\AuthController', 'logout']);
    $r->addRoute('GET', '/mypage', ['App\Controllers\AuthController', 'mypage']);
    $r->addRoute('POST', '/profile/update', ['App\Controllers\AuthController', 'updateProfile']);
    $r->addRoute('POST', '/profile/delete', ['App\Controllers\AuthController', 'deleteAccount']);
    $r->addRoute('GET', '/auth/check-duplicate', ['App\Controllers\AuthController', 'checkDuplicate']);

    // Admin Routes
    $r->addRoute('GET', '/admin', ['App\Controllers\AdminController', 'index']);
    $r->addRoute('GET', '/admin/config', ['App\Controllers\AdminController', 'config']);
    $r->addRoute('POST', '/admin/config', ['App\Controllers\AdminController', 'updateConfig']);
    $r->addRoute('POST', '/admin/config/create-template', ['App\Controllers\AdminController', 'createTemplate']);
    $r->addRoute('POST', '/admin/config/upload-image', ['App\Controllers\AdminController', 'uploadImage']);
    $r->addRoute('GET', '/admin/users', ['App\Controllers\AdminController', 'users']);
    $r->addRoute('POST', '/admin/users/create', ['App\Controllers\AdminController', 'createUser']);
    $r->addRoute('POST', '/admin/users/update', ['App\Controllers\AdminController', 'updateUser']);
    $r->addRoute('POST', '/admin/users/delete', ['App\Controllers\AdminController', 'deleteUser']);
    $r->addRoute('GET', '/admin/groups', ['App\Controllers\AdminController', 'groups']);
    $r->addRoute('POST', '/admin/groups', ['App\Controllers\AdminController', 'createGroup']);
    $r->addRoute('POST', '/admin/groups/update', ['App\Controllers\AdminController', 'updateGroup']);
    $r->addRoute('POST', '/admin/groups/delete', ['App\Controllers\AdminController', 'deleteGroup']);
    $r->addRoute('GET', '/admin/boards', ['App\Controllers\AdminController', 'boards']);
    $r->addRoute('POST', '/admin/boards', ['App\Controllers\AdminController', 'createBoard']);
    $r->addRoute('POST', '/admin/boards/update', ['App\Controllers\AdminController', 'updateBoard']);
    $r->addRoute('POST', '/admin/boards/delete', ['App\Controllers\AdminController', 'deleteBoard']);
    $r->addRoute('GET', '/admin/visitors', ['App\Controllers\AdminController', 'visitors']);
    $r->addRoute('POST', '/admin/visitors/cleanup', ['App\Controllers\AdminController', 'cleanupVisitors']);
    $r->addRoute('POST', '/admin/visitors/save-ips', ['App\Controllers\AdminController', 'saveVisitorIps']);
    $r->addRoute('GET', '/admin/point', ['App\Controllers\AdminController', 'point']);
    $r->addRoute('POST', '/admin/point/update', ['App\Controllers\AdminController', 'updatePoint']);
    $r->addRoute('POST', '/admin/point/bulk-delete', ['App\Controllers\AdminController', 'bulkDeletePoints']);
    
    // Mail Routes
    $r->addRoute('GET', '/admin/mail', ['App\Controllers\AdminController', 'mailForm']);
    $r->addRoute('POST', '/admin/mail/send', ['App\Controllers\AdminController', 'sendMail']);
    $r->addRoute('GET', '/admin/mail/logs', ['App\Controllers\AdminController', 'mailLogs']);
    $r->addRoute('POST', '/admin/mail/config', ['App\Controllers\AdminController', 'saveMailConfig']);
    $r->addRoute('POST', '/admin/mail/bulk-delete', ['App\Controllers\AdminController', 'bulkDeleteMailLogs']);

    // FAQ Manager Routes
    $r->addRoute('GET', '/admin/faq', ['App\Controllers\AdminController', 'faq']);
    $r->addRoute('POST', '/admin/faq/config', ['App\Controllers\AdminController', 'updateFaqConfig']);
    $r->addRoute('POST', '/admin/faq/create', ['App\Controllers\AdminController', 'createFaq']);
    $r->addRoute('POST', '/admin/faq/update', ['App\Controllers\AdminController', 'updateFaq']);
    $r->addRoute('POST', '/admin/faq/delete', ['App\Controllers\AdminController', 'deleteFaq']);

    // Page Manager Routes
    $r->addRoute('GET', '/admin/pages', ['App\Controllers\AdminController', 'pages']);
    $r->addRoute(['GET', 'POST'], '/admin/pages/create', ['App\Controllers\AdminController', 'createPage']);
    $r->addRoute(['GET', 'POST'], '/admin/pages/edit/{id:\d+}', ['App\Controllers\AdminController', 'editPage']);
    $r->addRoute('POST', '/admin/pages/delete', ['App\Controllers\AdminController', 'deletePage']);
    $r->addRoute('POST', '/admin/pages/upload-image', ['App\Controllers\AdminController', 'uploadPageImage']);

    // Board Routes
    $r->addRoute('GET', '/board/{slug}', ['App\Controllers\BoardController', 'index']);
    $r->addRoute('GET', '/board/view/{id:\d+}', ['App\Controllers\BoardController', 'show']);
    $r->addRoute(['GET', 'POST'], '/board/write/{slug}', ['App\Controllers\BoardController', 'write']);
    $r->addRoute(['GET', 'POST'], '/board/edit/{id:\d+}', ['App\Controllers\BoardController', 'edit']);
    $r->addRoute('POST', '/board/delete/{id:\d+}', ['App\Controllers\BoardController', 'delete']);
    $r->addRoute('POST', '/board/bulk-delete', ['App\Controllers\BoardController', 'bulkDelete']);
    $r->addRoute('POST', '/board/upload-image/{slug}', ['App\Controllers\BoardController', 'uploadEditorImage']);
    $r->addRoute('GET', '/board/download/{id:\d+}', ['App\Controllers\BoardController', 'download']);
    
    // Reply (답글) Routes
    $r->addRoute('POST', '/board/reply/{id:\d+}', ['App\Controllers\BoardController', 'writeReply']);
    $r->addRoute('POST', '/board/reply/delete/{id:\d+}', ['App\Controllers\BoardController', 'deleteReply']);
    
    // Comment (댓글) Routes
    $r->addRoute('POST', '/board/comment/add', ['App\Controllers\BoardController', 'addComment']);
    $r->addRoute('POST', '/board/comment/delete', ['App\Controllers\BoardController', 'deleteComment']);

    // Generic Page Route (must be near bottom)
    $r->addRoute('GET', '/page/{slug}', ['App\Controllers\HomeController', 'page']);

    // FAQ Route
    $r->addRoute('GET', '/faq', ['App\Controllers\HomeController', 'faq']);

    // Frontend Routes
    $r->addRoute('GET', '/', ['App\Controllers\HomeController', 'index']);
    $r->addRoute('GET', '/about', ['App\Controllers\HomeController', 'about']);
    $r->addRoute('GET', '/website', ['App\Controllers\HomeController', 'website']);
    $r->addRoute('POST', '/website/portfolio/add', ['App\Controllers\HomeController', 'addPortfolio']);
    $r->addRoute('POST', '/website/portfolio/delete', ['App\Controllers\HomeController', 'deletePortfolio']);
    $r->addRoute('POST', '/contact/send', ['App\Controllers\HomeController', 'sendContact']);

    // Payment Routes (Paddle)
    $r->addRoute('GET', '/payment', ['App\Controllers\PaymentController', 'index']);
    $r->addRoute('GET', '/payment/checkout/{plan_id}', ['App\Controllers\PaymentController', 'checkout']);
    $r->addRoute('GET', '/payment/success', ['App\Controllers\PaymentController', 'success']);
    $r->addRoute('POST', '/payment/webhook', ['App\Controllers\PaymentController', 'webhook']);

    // Payment Routes (Bootpay)
    $r->addRoute('POST', '/api/bootpay/save-billing', ['App\Controllers\BootpayController', 'saveBillingKey']);
    $r->addRoute('POST', '/api/bootpay/verify-addon', ['App\Controllers\BootpayController', 'verifyAddon']);
    $r->addRoute('POST', '/api/bootpay/cancel', ['App\Controllers\BootpayController', 'cancelBilling']);
    $r->addRoute('GET', '/subscribe', ['App\Controllers\BootpayController', 'subscribeForm']);
    $r->addRoute('GET', '/vendor/payments', ['App\Controllers\BootpayController', 'mypagePayments']);
    $r->addRoute('GET', '/admin/payments', ['App\Controllers\AdminController', 'bootpayPayments']);

    // Admin Product Management
    $r->addRoute('GET', '/admin/products', ['App\Controllers\AdminController', 'products']);
    $r->addRoute('POST', '/admin/products/create', ['App\Controllers\AdminController', 'createProduct']);
    $r->addRoute('POST', '/admin/products/update', ['App\Controllers\AdminController', 'updateProduct']);
    $r->addRoute('POST', '/admin/products/delete', ['App\Controllers\AdminController', 'deleteProduct']);
    // Mall Management 
    $r->addRoute('GET', '/admin/orders', [App\Controllers\AdminController::class, 'orders']);
    $r->addRoute('GET', '/admin/sellers', [App\Controllers\AdminController::class, 'sellers']);
    $r->addRoute('GET', '/admin/settlements', [App\Controllers\AdminController::class, 'settlements']);
    $r->addRoute('POST', '/admin/settlements/approve', [App\Controllers\AdminController::class, 'approveSettlement']);
    $r->addRoute('POST', '/admin/settlements/delete', [App\Controllers\AdminController::class, 'deleteSettlement']);
    // Seller Routes
    $r->addRoute('GET', '/seller/products', [App\Controllers\SellerController::class, 'products']);
    $r->addRoute('POST', '/seller/products/create', [App\Controllers\SellerController::class, 'createProduct']);
    $r->addRoute('GET', '/seller/orders', [App\Controllers\SellerController::class, 'orders']);
    $r->addRoute('POST', '/seller/orders/update-status', [App\Controllers\SellerController::class, 'updateOrderStatus']);
    $r->addRoute('GET', '/seller/settlement', [App\Controllers\SellerController::class, 'settlement']);
    $r->addRoute('POST', '/seller/settlement/request', [App\Controllers\SellerController::class, 'requestSettlement']);

    // Secure Downloads
    $r->addRoute('GET', '/order/download/{id:\d+}', ['App\Controllers\PaymentController', 'secureDownload']);
    $r->addRoute('POST', '/payment/confirm-order', ['App\Controllers\PaymentController', 'confirmOrder']);

    // SaaS Vendor Routes
    $r->addRoute('GET', '/vendor', ['App\Controllers\VendorController', 'index']);
    $r->addRoute('GET', '/vendor/', ['App\Controllers\VendorController', 'index']);
    $r->addRoute('GET', '/vendor/employees/{id:\d+}/logs', ['App\Controllers\VendorEmployeeController', 'logs']);
    $r->addRoute('GET', '/vendor/profiles', ['App\Controllers\VendorEmployeeController', 'profiles']);
    $r->addRoute('POST', '/vendor/profiles/login', ['App\Controllers\VendorEmployeeController', 'profileLogin']);
    $r->addRoute(['GET', 'POST'], '/vendor/employees', ['App\Controllers\VendorEmployeeController', 'index']);
    $r->addRoute('POST', '/vendor/employees/create', ['App\Controllers\VendorEmployeeController', 'create']);
    $r->addRoute('POST', '/vendor/employees/update', ['App\Controllers\VendorEmployeeController', 'update']);
    $r->addRoute('POST', '/vendor/employees/delete', ['App\Controllers\VendorEmployeeController', 'delete']);
    $r->addRoute(['GET', 'POST'], '/vendor/settings', ['App\Controllers\VendorController', 'settings']);
    $r->addRoute(['GET', 'POST'], '/vendor/pricing', ['App\Controllers\VendorController', 'pricing']);
    $r->addRoute('POST', '/vendor/pricing/supplier/add', ['App\Controllers\VendorController', 'addSupplier']);
    $r->addRoute('POST', '/vendor/pricing/manual_save', ['App\Controllers\VendorController', 'saveManualPricing']);
    $r->addRoute('GET', '/vendor/pricing/download', ['App\Controllers\VendorController', 'downloadPricingExcel']);
    $r->addRoute('POST', '/vendor/pricing/delete', ['App\Controllers\VendorController', 'deletePricingRules']);
    $r->addRoute('GET',  '/vendor/quotes',        ['App\Controllers\VendorController', 'quotes']);
    $r->addRoute('GET',  '/vendor/quotes/{id:\d+}', ['App\Controllers\VendorController', 'quoteDetail']);
    $r->addRoute('GET',  '/vendor/inquiries/{id:\d+}', ['App\Controllers\VendorController', 'inquiryDetail']);
    $r->addRoute('GET',  '/vendor/quotes/{id:\d+}/price', ['App\Controllers\VendorController', 'quotePrice']);
    $r->addRoute('POST', '/vendor/quotes/{id:\d+}/save_details', ['App\Controllers\VendorController', 'saveQuoteDetails']);
    $r->addRoute('POST', '/vendor/quotes/{id:\d+}/reset_details', ['App\Controllers\VendorController', 'resetQuoteDetails']);
    $r->addRoute('GET',  '/vendor/quotes/{id:\d+}/document', ['App\Controllers\VendorController', 'quoteDocument']);
    $r->addRoute('POST', '/vendor/quotes/{id:\d+}/send_email', ['App\Controllers\VendorController', 'sendEmail']);
    $r->addRoute('GET',  '/vendor/addon_payment', ['App\Controllers\VendorController', 'addonPayment']);
    $r->addRoute('GET',  '/vendor/embed',         ['App\Controllers\VendorController', 'embed']);
    $r->addRoute('GET',  '/vendor/share',         ['App\Controllers\VendorController', 'embed']);
    $r->addRoute('GET',  '/quote/{slug}/video',   ['App\Controllers\CanvasController', 'showVideoManual']);
    $r->addRoute('GET',  '/quote/{slug}/easy',    ['App\Controllers\CanvasController', 'showEasyCanvas']);
    $r->addRoute('GET',  '/quote/{slug}/cad',     ['App\Controllers\CanvasController', 'showVendorCanvas']);
    $r->addRoute('GET',  '/quote/{slug}',         ['App\Controllers\CanvasController', 'showBoardCanvas']);
    $r->addRoute('POST', '/api/canvas/analyze',   ['App\Controllers\CanvasController', 'analyzeLayout']);
    $r->addRoute('POST', '/quote/submit',         ['App\Controllers\CanvasController', 'submitQuote']);
    $r->addRoute('POST', '/review/submit',        ['App\Controllers\CanvasController', 'submitReview']);
};
