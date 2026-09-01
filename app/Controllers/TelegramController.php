<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Mailer;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\TelegramSession;
use App\Models\TelegramUser;
use App\Models\User;
use App\Models\VerificationCode;
use App\Services\TelegramService;
use Throwable;

final class TelegramController extends Controller
{
    private TelegramUser $telegramUsers;
    private TelegramSession $sessions;
    private Product $products;
    private TelegramService $telegram;
    private User $users;
    private VerificationCode $codes;
    private Mailer $mailer;
    private Cart $cart;
    private Address $addresses;
    private Order $orders;

    public function __construct()
    {
        $this->telegramUsers = new TelegramUser();
        $this->sessions = new TelegramSession();
        $this->products = new Product();
        $this->telegram = new TelegramService();
        $this->users = new User();
        $this->codes = new VerificationCode();
        $this->mailer = new Mailer();
        $this->cart = new Cart();
        $this->addresses = new Address();
        $this->orders = new Order();
    }

    /**
     * Process a Telegram update.
     *
     * Used by the long-polling worker.
     */
    public function processUpdate(array $update): void
    {
        $this->handleUpdate($update);
    }

    /**
     * Telegram webhook endpoint.
     */
    public function webhook(): void
    {
        header(
            'Content-Type: application/json; charset=utf-8'
        );

        try {
            $rawInput = file_get_contents(
                'php://input'
            );

            if (
                $rawInput === false
                || trim($rawInput) === ''
            ) {
                echo json_encode([
                    'ok' => true,
                ]);

                return;
            }

            $update = json_decode(
                $rawInput,
                true
            );

            if (!is_array($update)) {
                http_response_code(400);

                echo json_encode([
                    'ok' => false,
                    'message' => 'Invalid update.',
                ]);

                return;
            }

            $this->handleUpdate($update);

            echo json_encode([
                'ok' => true,
            ]);
        } catch (Throwable $exception) {
            http_response_code(500);

            echo json_encode([
                'ok' => false,
                'message' => 'Webhook processing failed.',
            ]);
        }
    }

    /**
     * Process Telegram update.
     */
    private function handleUpdate(
        array $update
    ): void {
        /*
         * ==============================
         * Callback Query
         * ==============================
         */
        $callbackQuery =
            $update['callback_query']
            ?? null;

        if (is_array($callbackQuery)) {
            $this->handleCallbackQuery(
                $callbackQuery
            );

            return;
        }

        /*
         * ==============================
         * Message
         * ==============================
         */
        $message =
            $update['message']
            ?? null;

        if (!is_array($message)) {
            return;
        }

        $chat =
            $message['chat']
            ?? null;

        $from =
            $message['from']
            ?? null;

        if (
            !is_array($chat)
            || !is_array($from)
        ) {
            return;
        }

        $chatId =
            $chat['id']
            ?? null;

        if ($chatId === null) {
            return;
        }

        $text = trim(
            (string) (
                $message['text']
                ?? ''
            )
        );

        /*
         * ==============================
         * Sync Telegram User
         * ==============================
         */
        $telegramId = (int) (
            $from['id']
            ?? 0
        );

        if ($telegramId <= 0) {
            return;
        }

        $this->telegramUsers->sync(
            $telegramId,
            isset($from['username'])
                ? (string) $from['username']
                : null,
            isset($from['first_name'])
                ? (string) $from['first_name']
                : null,
            isset($from['last_name'])
                ? (string) $from['last_name']
                : null
        );

        /*
         * ==============================
         * /start
         * ==============================
         */
        if (
            $text === '/start'
            || str_starts_with(
                $text,
                '/start '
            )
        ) {
            $this->sessions->clear(
                $telegramId
            );

            $firstName = trim(
                (string) (
                    $from['first_name']
                    ?? ''
                )
            );

            $name =
                $firstName !== ''
                ? $firstName
                : 'دوست عزیز';

            $this->sendMainMenu(
                $chatId,
                $name
            );

            return;
        }

        /*
         * ==============================
         * /help
         * ==============================
         */
        if ($text === '/help') {
            $this->sessions->clear(
                $telegramId
            );

            $this->sendMainMenu(
                $chatId,
                'دوست عزیز'
            );

            return;
        }

        /*
         * ==============================
         * Search state
         * ==============================
         */
        $state = $this->sessions->getState(
            $telegramId
        );

        if ($state === 'searching') {
            $this->handleProductSearch(
                $chatId,
                $telegramId,
                $text
            );

            return;
        }

        /*
         * ==============================
         * Telegram account linking
         * ==============================
         */
        if ($state === 'telegram_link_email') {
            $this->handleTelegramLinkEmail(
                $chatId,
                $telegramId,
                $text
            );

            return;
        }

        if ($state === 'telegram_link_code') {
            $this->handleTelegramLinkCode(
                $chatId,
                $telegramId,
                $text
            );

            return;
        }

        /*
         * ==============================
         * Unknown message
         * ==============================
         */
        $this->telegram->sendMessage(
            $chatId,
            "دستور دریافت شد، اما این قابلیت هنوز فعال نشده است.\n\n"
                . "برای مشاهده منوی اصلی /start را ارسال کنید."
        );
    }

    /**
     * Send main menu.
     */
    private function sendMainMenu(
        int|string $chatId,
        string $name
    ): void {
        $keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => '🔎 جستجوی محصولات',
                        'callback_data' => 'products_search',
                    ],
                ],
                [
                    [
                        'text' => '🔐 اتصال حساب کاربری',
                        'callback_data' => 'account_link',
                    ],
                ],
                [
                    [
                        'text' => '🚪 خروج از حساب',
                        'callback_data' => 'account_logout',
                    ],
                ],
                [
                    [
                        'text' => '🛒 سبد خرید',
                        'callback_data' => 'cart',
                    ],
                    [
                        'text' => '📦 سفارش‌های من',
                        'callback_data' => 'orders',
                    ],
                ],
                [
                    [
                        'text' => 'ℹ️ راهنما',
                        'callback_data' => 'help',
                    ],
                ],
            ],
        ];

        $this->telegram->sendMessage(
            $chatId,
            "سلام {$name} 👋\n\n"
                . "به فروشگاه Arvand Audio Store خوش آمدید. 🎧\n\n"
                . "از منوی زیر استفاده کنید:",
            $keyboard
        );
    }

    /**
     * Handle callback queries.
     */
    private function handleCallbackQuery(
        array $callbackQuery
    ): void {
        $id =
            (string) (
                $callbackQuery['id']
                ?? ''
            );

        $data =
            (string) (
                $callbackQuery['data']
                ?? ''
            );

        $message =
            $callbackQuery['message']
            ?? null;

        $from =
            $callbackQuery['from']
            ?? null;

        if (
            $id === ''
            || !is_array($message)
            || !is_array($from)
        ) {
            return;
        }

        $chat =
            $message['chat']
            ?? null;

        if (!is_array($chat)) {
            return;
        }

        $chatId =
            $chat['id']
            ?? null;

        if ($chatId === null) {
            return;
        }

        $telegramId = (int) (
            $from['id']
            ?? 0
        );

        if ($telegramId <= 0) {
            return;
        }

        /*
         * Make sure callback user exists
         * in telegram_users.
         */
        $this->telegramUsers->sync(
            $telegramId,
            isset($from['username'])
                ? (string) $from['username']
                : null,
            isset($from['first_name'])
                ? (string) $from['first_name']
                : null,
            isset($from['last_name'])
                ? (string) $from['last_name']
                : null
        );

        /*
         * Telegram requires every callback
         * query to be acknowledged.
         */
        $this->telegram->answerCallbackQuery(
            $id
        );

        /*
         * ==============================
         * Search
         * ==============================
         */
        if ($data === 'products_search') {
            $this->sessions->setState(
                $telegramId,
                'searching'
            );

            $this->telegram->sendMessage(
                $chatId,
                "🔎 جستجوی محصولات\n\n"
                    . "نام، مدل یا برند محصول مورد نظر را ارسال کنید.\n\n"
                    . "مثلاً:\n"
                    . "Shure\n"
                    . "Focusrite\n"
                    . "SM7B"
            );

            return;
        }

        /*
         * ==============================
         * Product details
         * ==============================
         */
        if (
            str_starts_with(
                $data,
                'product:'
            )
        ) {
            $productId = (int) substr(
                $data,
                strlen('product:')
            );

            if ($productId <= 0) {
                return;
            }

            $this->showProduct(
                $chatId,
                $productId
            );

            return;
        }

        /*
         * ==============================
         * Add product to cart
         * ==============================
         */
        if (
            str_starts_with(
                $data,
                'cart_add:'
            )
        ) {
            $productId = (int) substr(
                $data,
                strlen('cart_add:')
            );

            if ($productId <= 0) {
                $this->telegram->sendMessage(
                    $chatId,
                    "❌ محصول نامعتبر است."
                );

                return;
            }

            /*
             * Get linked website user.
             */
            $userId = $this->telegramUsers->getLinkedUserId(
                $telegramId
            );

            if ($userId === null) {
                $this->telegram->sendMessage(
                    $chatId,
                    "🔐 حساب تلگرام شما هنوز به حساب فروشگاه متصل نیست.\n\n"
                        . "لطفاً ابتدا حساب خود را متصل کنید."
                );

                return;
            }

            /*
             * Check product.
             */
            $product = $this->products->findById(
                $productId
            );

            if ($product === null) {
                $this->telegram->sendMessage(
                    $chatId,
                    "❌ محصول مورد نظر پیدا نشد."
                );

                return;
            }

            /*
             * Check stock.
             */
            $stock = (int) (
                $product['stock']
                ?? 0
            );

            if ($stock <= 0) {
                $this->telegram->sendMessage(
                    $chatId,
                    "❌ این محصول در حال حاضر ناموجود است."
                );

                return;
            }

            /*
             * Add one item to cart.
             */
            $this->cart->add(
                $userId,
                $productId,
                1
            );

            $name = (string) (
                $product['name']
                ?? 'محصول'
            );

            $keyboard = [
                'inline_keyboard' => [
                    [
                        [
                            'text' => '🛒 مشاهده سبد خرید',
                            'callback_data' => 'cart',
                        ],
                    ],
                    [
                        [
                            'text' => '🔎 جستجوی محصولات',
                            'callback_data' => 'products_search',
                        ],
                        [
                            'text' => '🏠 منوی اصلی',
                            'callback_data' => 'main_menu',
                        ],
                    ],
                ],
            ];

            $this->telegram->sendMessage(
                $chatId,
                "✅ محصول به سبد خرید اضافه شد! 🎉\n\n"
                    . "🎧 {$name}\n"
                    . "🔢 تعداد: 1\n\n"
                    . "برای مشاهده سبد خرید روی دکمه زیر بزنید.",
                $keyboard
            );

            return;
        }

        /*
         * ==============================
         * Increase cart item
         * ==============================
         */
        if (
            str_starts_with(
                $data,
                'cart_increase:'
            )
        ) {
            $productId = (int) substr(
                $data,
                strlen('cart_increase:')
            );

            if ($productId <= 0) {
                return;
            }

            $userId = $this->telegramUsers->getLinkedUserId(
                $telegramId
            );

            if ($userId === null) {
                $this->telegram->sendMessage(
                    $chatId,
                    "🔐 حساب تلگرام شما متصل نیست."
                );

                return;
            }

            $item = $this->cart->findItem(
                $userId,
                $productId
            );

            if ($item === null) {
                $this->telegram->sendMessage(
                    $chatId,
                    "❌ این محصول در سبد خرید شما وجود ندارد."
                );

                return;
            }

            $product = $this->products->findById(
                $productId
            );

            if ($product === null) {
                $this->telegram->sendMessage(
                    $chatId,
                    "❌ محصول پیدا نشد."
                );

                return;
            }

            $currentQuantity = (int) (
                $item['quantity']
                ?? 0
            );

            $stock = (int) (
                $product['stock']
                ?? 0
            );

            if (
                $currentQuantity >= $stock
            ) {
                $this->telegram->sendMessage(
                    $chatId,
                    "⚠️ امکان افزایش بیشتر وجود ندارد.\n\n"
                        . "📦 موجودی فعلی: {$stock} عدد"
                );

                return;
            }

            $newQuantity = $currentQuantity + 1;

            $this->cart->updateQuantity(
                $userId,
                $productId,
                $newQuantity
            );

            $this->showCart(
                $chatId,
                $telegramId
            );

            return;
        }

        /*
         * ==============================
         * Decrease cart item
         * ==============================
         */
        if (
            str_starts_with(
                $data,
                'cart_decrease:'
            )
        ) {
            $productId = (int) substr(
                $data,
                strlen('cart_decrease:')
            );

            if ($productId <= 0) {
                return;
            }

            $userId = $this->telegramUsers->getLinkedUserId(
                $telegramId
            );

            if ($userId === null) {
                $this->telegram->sendMessage(
                    $chatId,
                    "🔐 حساب تلگرام شما متصل نیست."
                );

                return;
            }

            $item = $this->cart->findItem(
                $userId,
                $productId
            );

            if ($item === null) {
                $this->showCart(
                    $chatId,
                    $telegramId
                );

                return;
            }

            $currentQuantity = (int) (
                $item['quantity']
                ?? 0
            );

            /*
             * If quantity is 1,
             * remove the product.
             */
            if ($currentQuantity <= 1) {
                $this->cart->remove(
                    $userId,
                    $productId
                );
            } else {
                $this->cart->updateQuantity(
                    $userId,
                    $productId,
                    $currentQuantity - 1
                );
            }

            $this->showCart(
                $chatId,
                $telegramId
            );

            return;
        }

        /*
         * ==============================
         * Remove cart item
         * ==============================
         */
        if (
            str_starts_with(
                $data,
                'cart_remove:'
            )
        ) {
            $productId = (int) substr(
                $data,
                strlen('cart_remove:')
            );

            if ($productId <= 0) {
                return;
            }

            $userId = $this->telegramUsers->getLinkedUserId(
                $telegramId
            );

            if ($userId === null) {
                $this->telegram->sendMessage(
                    $chatId,
                    "🔐 حساب تلگرام شما متصل نیست."
                );

                return;
            }

            $this->cart->remove(
                $userId,
                $productId
            );

            $this->showCart(
                $chatId,
                $telegramId
            );

            return;
        }

        /*
         * ==============================
         * Clear cart
         * ==============================
         */
        if ($data === 'cart_clear') {
            $userId = $this->telegramUsers->getLinkedUserId(
                $telegramId
            );

            if ($userId === null) {
                $this->telegram->sendMessage(
                    $chatId,
                    "🔐 حساب تلگرام شما متصل نیست."
                );

                return;
            }

            $this->cart->clear(
                $userId
            );

            $this->showCart(
                $chatId,
                $telegramId
            );

            return;
        }

        /*
         * ==============================
         * No-op cart button
         * ==============================
         */
        if ($data === 'cart_noop') {
            return;
        }

        /*
         * ==============================
         * Back to main menu
         * ==============================
         */
        if ($data === 'main_menu') {
            $this->sessions->clear(
                $telegramId
            );

            $this->sendMainMenu(
                $chatId,
                'دوست عزیز'
            );

            return;
        }

        /*
         * ==============================
         * Account link
         * ==============================
         */
        if ($data === 'account_link') {
            $linkedUserId =
                $this->telegramUsers->getLinkedUserId(
                    $telegramId
                );

            if ($linkedUserId !== null) {
                $user = $this->users->findById(
                    $linkedUserId
                );

                $name = $user !== null
                    ? (string) ($user['name'] ?? 'کاربر')
                    : 'کاربر';

                $this->telegram->sendMessage(
                    $chatId,
                    "✅ حساب شما قبلاً متصل شده است.\n\n"
                        . "👤 {$name}\n\n"
                        . "اکنون می‌توانید از سبد خرید و "
                        . "امکانات خرید استفاده کنید."
                );

                return;
            }

            $this->sessions->setState(
                $telegramId,
                'telegram_link_email'
            );

            $this->telegram->sendMessage(
                $chatId,
                "🔐 اتصال حساب کاربری\n\n"
                    . "برای اتصال حساب فروشگاه به تلگرام، "
                    . "ایمیل حساب کاربری خود را ارسال کنید.\n\n"
                    . "مثال:\n"
                    . "test@example.com"
            );

            return;
        }

        /*
         * ==============================
         * Account logout
         * ==============================
         */
        if ($data === 'account_logout') {
            $linkedUserId =
                $this->telegramUsers->getLinkedUserId(
                    $telegramId
                );

            if ($linkedUserId === null) {
                $this->telegram->sendMessage(
                    $chatId,
                    "ℹ️ حسابی به تلگرام شما متصل نیست.",
                    [
                        'inline_keyboard' => [
                            [
                                [
                                    'text' => '🔐 اتصال حساب',
                                    'callback_data' => 'account_link',
                                ],
                            ],
                            [
                                [
                                    'text' => '🏠 منوی اصلی',
                                    'callback_data' => 'main_menu',
                                ],
                            ],
                        ],
                    ]
                );

                return;
            }

            $this->telegramUsers->unlinkFromUser(
                $telegramId
            );

            $this->sessions->clear(
                $telegramId
            );

            $this->telegram->sendMessage(
                $chatId,
                "✅ با موفقیت از حساب فروشگاه خارج شدید.\n\n"
                    . "🔐 اتصال حساب تلگرام شما حذف شد.\n\n"
                    . "برای استفاده از امکانات خرید، "
                    . "دوباره حساب خود را متصل کنید.",
                [
                    'inline_keyboard' => [
                        [
                            [
                                'text' => '🔐 اتصال حساب',
                                'callback_data' => 'account_link',
                            ],
                        ],
                        [
                            [
                                'text' => '🏠 منوی اصلی',
                                'callback_data' => 'main_menu',
                            ],
                        ],
                    ],
                ]
            );

            return;
        }

        /*
         * ==============================
         * Checkout
         * ==============================
         */
        if ($data === 'checkout') {
            $this->showCheckout(
                $chatId,
                $telegramId
            );

            return;
        }

        /*
         * ==============================
         * Checkout address selection
         * ==============================
         */
        if (
            str_starts_with(
                $data,
                'checkout_address:'
            )
        ) {
            $addressId = (int) substr(
                $data,
                strlen('checkout_address:')
            );

            if ($addressId <= 0) {
                return;
            }

            $this->showCheckoutConfirmation(
                $chatId,
                $telegramId,
                $addressId
            );

            return;
        }

        /*
         * ==============================
         * Checkout confirmation
         * ==============================
         */
        if (
            str_starts_with(
                $data,
                'checkout_confirm:'
            )
        ) {
            $addressId = (int) substr(
                $data,
                strlen('checkout_confirm:')
            );

            if ($addressId <= 0) {
                return;
            }

            $this->confirmCheckout(
                $chatId,
                $telegramId,
                $addressId
            );

            return;
        }

        /*
         * ==============================
         * Checkout cancel
         * ==============================
         */
        if ($data === 'checkout_cancel') {
            $this->showCart(
                $chatId,
                $telegramId
            );

            return;
        }

        /*
         * ==============================
         * Cart
         * ==============================
         */
        if ($data === 'cart') {
            $this->showCart(
                $chatId,
                $telegramId
            );

            return;
        }

        /*
         * ==============================
         * Order details
         * ==============================
         */
        if (
            str_starts_with(
                $data,
                'order:'
            )
        ) {
            $orderId = (int) substr(
                $data,
                strlen('order:')
            );

            if ($orderId <= 0) {
                return;
            }

            $this->showOrder(
                $chatId,
                $telegramId,
                $orderId
            );

            return;
        }

        /*
         * ==============================
         * Orders
         * ==============================
         */
        if ($data === 'orders') {
            $this->showOrders(
                $chatId,
                $telegramId
            );

            return;
        }

        /*
         * ==============================
         * Help
         * ==============================
         */
        if ($data === 'help') {
            $this->telegram->sendMessage(
                $chatId,
                "ℹ️ راهنمای فروشگاه\n\n"
                    . "🔎 برای جستجوی محصول، "
                    . "گزینه جستجوی محصولات را انتخاب کنید.\n\n"
                    . "🛒 برای مشاهده سبد خرید، "
                    . "گزینه سبد خرید را بزنید.\n\n"
                    . "📦 برای مشاهده سفارش‌ها، "
                    . "گزینه سفارش‌های من را انتخاب کنید."
            );

            return;
        }
    }

    /**
     * Handle product search.
     */
    private function handleProductSearch(
        int|string $chatId,
        int $telegramId,
        string $keyword
    ): void {
        if ($keyword === '') {
            $this->telegram->sendMessage(
                $chatId,
                "🔎 لطفاً نام یا مدل محصول را ارسال کنید."
            );

            return;
        }

        /*
         * Save current search keyword.
         */
        $this->sessions->setState(
            $telegramId,
            'searching',
            [
                'keyword' => $keyword,
            ]
        );

        $results = $this->products->search(
            $keyword,
            10
        );

        if ($results === []) {
            $this->telegram->sendMessage(
                $chatId,
                "❌ محصولی برای «{$keyword}» پیدا نشد.\n\n"
                    . "یک نام، مدل یا برند دیگر را امتحان کنید."
            );

            return;
        }

        $text =
            "🔎 نتایج جستجو برای «{$keyword}»\n\n";

        $keyboard = [
            'inline_keyboard' => [],
        ];

        foreach ($results as $product) {
            $name = (string) (
                $product['name']
                ?? 'محصول'
            );

            $price = $this->formatPrice(
                $product
            );

            $stock = (int) (
                $product['stock']
                ?? 0
            );

            $text .= "🎧 {$name}\n";
            $text .= "💰 {$price}\n";

            if ($stock > 0) {
                $text .= "📦 موجود\n\n";
            } else {
                $text .= "❌ ناموجود\n\n";
            }

            $productId = (int) (
                $product['id']
                ?? 0
            );

            if ($productId > 0) {
                $keyboard['inline_keyboard'][] = [
                    [
                        'text' => "👁 مشاهده {$name}",
                        'callback_data' => "product:{$productId}",
                    ],
                ];
            }
        }

        $keyboard['inline_keyboard'][] = [
            [
                'text' => '🔎 جستجوی دوباره',
                'callback_data' => 'products_search',
            ],
            [
                'text' => '🏠 منوی اصلی',
                'callback_data' => 'main_menu',
            ],
        ];

        /*
         * Search finished.
         */
        $this->sessions->clear(
            $telegramId
        );

        $this->telegram->sendMessage(
            $chatId,
            $text,
            $keyboard
        );
    }

    /**
     * Show product details.
     */
    private function showProduct(
        int|string $chatId,
        int $productId
    ): void {
        $product = $this->products->findById(
            $productId
        );

        if ($product === null) {
            $this->telegram->sendMessage(
                $chatId,
                "❌ محصول مورد نظر پیدا نشد."
            );

            return;
        }

        $name = (string) (
            $product['name']
            ?? 'محصول'
        );

        $description = trim(
            (string) (
                $product['description']
                ?? ''
            )
        );

        $price = $this->formatPrice(
            $product
        );

        $stock = (int) (
            $product['stock']
            ?? 0
        );

        $brand = trim(
            (string) (
                $product['brand_name']
                ?? ''
            )
        );

        $category = trim(
            (string) (
                $product['category_name']
                ?? ''
            )
        );

        $text =
            "🎧 {$name}\n\n";

        if ($brand !== '') {
            $text .= "🏷 برند: {$brand}\n";
        }

        if ($category !== '') {
            $text .= "📂 دسته‌بندی: {$category}\n";
        }

        $text .= "💰 قیمت: {$price}\n";

        if ($stock > 0) {
            $text .= "📦 موجودی: {$stock} عدد\n";
        } else {
            $text .= "❌ وضعیت: ناموجود\n";
        }

        if ($description !== '') {
            $text .= "\n📝 {$description}\n";
        }

        $keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => '🛒 افزودن به سبد خرید',
                        'callback_data' => "cart_add:{$productId}",
                    ],
                ],
                [
                    [
                        'text' => '🔎 جستجوی محصولات',
                        'callback_data' => 'products_search',
                    ],
                    [
                        'text' => '🏠 منوی اصلی',
                        'callback_data' => 'main_menu',
                    ],
                ],
            ],
        ];

        $this->telegram->sendMessage(
            $chatId,
            $text,
            $keyboard
        );
    }

    /**
     * Handle Telegram account linking - receive email.
     */
    private function handleTelegramLinkEmail(
        int|string $chatId,
        int $telegramId,
        string $email
    ): void {
        $email = trim($email);

        if (
            $email === ''
            || !filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
        ) {
            $this->telegram->sendMessage(
                $chatId,
                "❌ ایمیل واردشده معتبر نیست.\n\n"
                    . "لطفاً ایمیل حساب فروشگاه را دوباره ارسال کنید."
            );

            return;
        }

        $user = $this->users->findByEmail(
            $email
        );

        if ($user === null) {
            $this->telegram->sendMessage(
                $chatId,
                "❌ حسابی با این ایمیل پیدا نشد.\n\n"
                    . "ابتدا در فروشگاه ثبت‌نام کنید و سپس دوباره تلاش کنید."
            );

            return;
        }

        $userId = (int) $user['id'];

        /*
         * Invalidate previous Telegram-link codes.
         */
        $this->codes->invalidatePrevious(
            $userId,
            'telegram_link'
        );

        /*
         * Generate 6-digit verification code.
         */
        $code = (string) random_int(
            100000,
            999999
        );

        $this->codes->create(
            $userId,
            'telegram_link',
            $code,
            10
        );

        /*
         * Save temporary linking information.
         */
        $this->sessions->setState(
            $telegramId,
            'telegram_link_code',
            [
                'user_id' => $userId,
                'email' => $email,
            ]
        );

        /*
         * Send verification code by email.
         */
        $this->mailer->sendVerificationCode(
            $email,
            (string) ($user['name'] ?? 'کاربر'),
            $code
        );

        $this->telegram->sendMessage(
            $chatId,
            "📨 کد تأیید برای ایمیل شما ارسال شد.\n\n"
                . "کد ارسال‌شده را همینجا وارد کنید.\n\n"
                . "⏱ اعتبار کد: ۱۰ دقیقه\n"
                . "🔢 کد ۶ رقمی است."
        );
    }

    /**
     * Handle Telegram account linking - verify code.
     */
    private function handleTelegramLinkCode(
        int|string $chatId,
        int $telegramId,
        string $code
    ): void {
        $code = trim($code);

        if (
            strlen($code) !== 6
            || !ctype_digit($code)
        ) {
            $this->telegram->sendMessage(
                $chatId,
                "❌ کد باید دقیقاً ۶ رقم باشد.\n\n"
                    . "لطفاً کد ارسال‌شده به ایمیل را وارد کنید."
            );

            return;
        }

        $sessionData = $this->sessions->getData(
            $telegramId
        );

        if (
            !is_array($sessionData)
            || !isset($sessionData['user_id'])
        ) {
            $this->sessions->clear(
                $telegramId
            );

            $this->telegram->sendMessage(
                $chatId,
                "❌ اطلاعات اتصال حساب پیدا نشد.\n\n"
                    . "لطفاً دوباره از منوی اصلی گزینه "
                    . "«اتصال حساب کاربری» را انتخاب کنید."
            );

            return;
        }

        $userId = (int) $sessionData['user_id'];

        $latest = $this->codes->findLatestActive(
            $userId,
            'telegram_link'
        );

        if ($latest === null) {
            $this->sessions->clear(
                $telegramId
            );

            $this->telegram->sendMessage(
                $chatId,
                "❌ کد منقضی شده یا دیگر معتبر نیست.\n\n"
                    . "لطفاً دوباره درخواست اتصال حساب بدهید."
            );

            return;
        }

        if (
            !$this->codes->verify(
                (int) $latest['id'],
                $code
            )
        ) {
            $this->telegram->sendMessage(
                $chatId,
                "❌ کد واردشده اشتباه است.\n\n"
                    . "لطفاً دوباره تلاش کنید."
            );

            return;
        }

        /*
         * Link Telegram account to website user.
         */
        $linked =
            $this->telegramUsers->linkToUser(
                $telegramId,
                $userId
            );

        if (!$linked) {
            $this->sessions->clear(
                $telegramId
            );

            $this->telegram->sendMessage(
                $chatId,
                "❌ اتصال حساب انجام نشد.\n\n"
                    . "لطفاً دوباره تلاش کنید."
            );

            return;
        }

        $this->sessions->clear(
            $telegramId
        );

        $user = $this->users->findById(
            $userId
        );

        $name = $user !== null
            ? (string) ($user['name'] ?? 'کاربر')
            : 'کاربر';

        $this->telegram->sendMessage(
            $chatId,
            "✅ حساب شما با موفقیت متصل شد! 🎉\n\n"
                . "👤 {$name}\n"
                . "📧 {$sessionData['email']}\n\n"
                . "اکنون می‌توانید از امکانات خرید فروشگاه "
                . "از طریق تلگرام استفاده کنید. 🛒"
        );
    }

    /**
     * Show Telegram user's cart.
     */
    private function showCart(
        int|string $chatId,
        int $telegramId
    ): void {
        $userId = $this->telegramUsers->getLinkedUserId(
            $telegramId
        );

        if ($userId === null) {
            $this->telegram->sendMessage(
                $chatId,
                "🔐 حساب تلگرام شما هنوز به حساب فروشگاه متصل نیست.\n\n"
                    . "لطفاً ابتدا حساب خود را متصل کنید."
            );

            return;
        }

        $items = $this->cart->getItems(
            $userId
        );

        if ($items === []) {
            $keyboard = [
                'inline_keyboard' => [
                    [
                        [
                            'text' => '🔎 جستجوی محصولات',
                            'callback_data' => 'products_search',
                        ],
                    ],
                    [
                        [
                            'text' => '🏠 منوی اصلی',
                            'callback_data' => 'main_menu',
                        ],
                    ],
                ],
            ];

            $this->telegram->sendMessage(
                $chatId,
                "🛒 سبد خرید شما خالی است.\n\n"
                    . "برای اضافه کردن محصول، جستجوی محصولات را انتخاب کنید.",
                $keyboard
            );

            return;
        }

        $text = "🛒 سبد خرید شما\n\n";

        $keyboard = [
            'inline_keyboard' => [],
        ];

        foreach ($items as $item) {
            $itemId = (int) (
                $item['cart_item_id']
                ?? 0
            );

            $productId = (int) (
                $item['product_id']
                ?? 0
            );

            $name = (string) (
                $item['name']
                ?? 'محصول'
            );

            $quantity = (int) (
                $item['quantity']
                ?? 0
            );

            $stock = (int) (
                $item['stock']
                ?? 0
            );

            $price = $this->formatPrice(
                $item
            );

            $unitPrice =
                $item['discount_price'] !== null
                && $item['discount_price'] !== ''
                ? (float) $item['discount_price']
                : (float) (
                    $item['price']
                    ?? 0
                );

            $itemTotal = $unitPrice * $quantity;

            $text .= "🎧 {$name}\n";
            $text .= "💰 قیمت واحد: "
                . number_format(
                    $unitPrice,
                    0,
                    '.',
                    ','
                )
                . " تومان\n";
            $text .= "🔢 تعداد: {$quantity}\n";
            $text .= "💵 مجموع: "
                . number_format(
                    $itemTotal,
                    0,
                    '.',
                    ','
                )
                . " تومان\n\n";

            if ($itemId > 0) {
                $keyboard['inline_keyboard'][] = [
                    [
                        'text' => '➖',
                        'callback_data' => "cart_decrease:{$productId}",
                    ],
                    [
                        'text' => "🔢 {$quantity}",
                        'callback_data' => 'cart_noop',
                    ],
                    [
                        'text' => '➕',
                        'callback_data' => "cart_increase:{$productId}",
                    ],
                    [
                        'text' => '🗑 حذف',
                        'callback_data' => "cart_remove:{$productId}",
                    ],
                ];
            }
        }

        $total = $this->cart->getTotal(
            $userId
        );

        $count = $this->cart->getItemCount(
            $userId
        );

        $text .= "━━━━━━━━━━━━━━\n";
        $text .= "📦 تعداد کالاها: {$count}\n";
        $text .= "💵 مبلغ نهایی: "
            . number_format(
                (float) $total,
                0,
                '.',
                ','
            )
            . " تومان";

        $keyboard['inline_keyboard'][] = [
            [
                'text' => '🧹 خالی کردن سبد',
                'callback_data' => 'cart_clear',
            ],
        ];

        $keyboard['inline_keyboard'][] = [
            [
                'text' => '🔎 ادامه خرید',
                'callback_data' => 'products_search',
            ],
        ];

        $keyboard['inline_keyboard'][] = [
            [
                'text' => '🛍 ثبت سفارش',
                'callback_data' => 'checkout',
            ],
        ];

        $keyboard['inline_keyboard'][] = [
            [
                'text' => '🏠 منوی اصلی',
                'callback_data' => 'main_menu',
            ],
        ];

        $this->telegram->sendMessage(
            $chatId,
            $text,
            $keyboard
        );
    }

    /**
     * Show checkout and user's addresses.
     */
    private function showCheckout(
        int|string $chatId,
        int $telegramId
    ): void {
        $userId = $this->telegramUsers->getLinkedUserId(
            $telegramId
        );

        if ($userId === null) {
            $this->telegram->sendMessage(
                $chatId,
                "🔐 حساب تلگرام شما هنوز به حساب فروشگاه متصل نیست.\n\n"
                    . "لطفاً ابتدا حساب خود را متصل کنید."
            );

            return;
        }

        $items = $this->cart->getItems(
            $userId
        );

        if ($items === []) {
            $this->telegram->sendMessage(
                $chatId,
                "🛒 سبد خرید شما خالی است.\n\n"
                    . "ابتدا یک محصول به سبد خرید اضافه کنید."
            );

            return;
        }

        $addresses = $this->addresses->getUserAddresses(
            $userId
        );

        if ($addresses === []) {
            $keyboard = [
                'inline_keyboard' => [
                    [
                        [
                            'text' => '🛒 بازگشت به سبد',
                            'callback_data' => 'cart',
                        ],
                    ],
                    [
                        [
                            'text' => '🏠 منوی اصلی',
                            'callback_data' => 'main_menu',
                        ],
                    ],
                ],
            ];

            $this->telegram->sendMessage(
                $chatId,
                "📦 ثبت سفارش\n\n"
                    . "برای ثبت سفارش هنوز هیچ آدرسی در حساب شما ثبت نشده است.\n\n"
                    . "لطفاً ابتدا از سایت فروشگاه یک آدرس ثبت کنید.",
                $keyboard
            );

            return;
        }

        $text =
            "📦 ثبت سفارش\n\n"
            . "لطفاً آدرس ارسال سفارش را انتخاب کنید:\n\n";

        $keyboard = [
            'inline_keyboard' => [],
        ];

        foreach ($addresses as $address) {
            $addressId = (int) (
                $address['id']
                ?? 0
            );

            if ($addressId <= 0) {
                continue;
            }

            $title = trim(
                (string) (
                    $address['title']
                    ?? ''
                )
            );

            $receiver = trim(
                (string) (
                    $address['receiver_name']
                    ?? ''
                )
            );

            $city = trim(
                (string) (
                    $address['city']
                    ?? ''
                )
            );

            $label = $title !== ''
                ? $title
                : 'آدرس';

            $buttonText =
                "📍 {$label}";

            if ($city !== '') {
                $buttonText .= " - {$city}";
            }

            $keyboard['inline_keyboard'][] = [
                [
                    'text' => $buttonText,
                    'callback_data' => "checkout_address:{$addressId}",
                ],
            ];

            if ($receiver !== '') {
                $text .= "📍 {$label}\n";
                $text .= "👤 {$receiver}\n";

                if ($city !== '') {
                    $text .= "🏙 {$city}\n";
                }

                $text .= "\n";
            }
        }

        $keyboard['inline_keyboard'][] = [
            [
                'text' => '🛒 بازگشت به سبد',
                'callback_data' => 'cart',
            ],
            [
                'text' => '🏠 منوی اصلی',
                'callback_data' => 'main_menu',
            ],
        ];

        $this->telegram->sendMessage(
            $chatId,
            $text,
            $keyboard
        );
    }

    /**
     * Show checkout confirmation.
     */
    private function showCheckoutConfirmation(
        int|string $chatId,
        int $telegramId,
        int $addressId
    ): void {
        $userId = $this->telegramUsers->getLinkedUserId(
            $telegramId
        );

        if ($userId === null) {
            $this->telegram->sendMessage(
                $chatId,
                "🔐 حساب تلگرام شما به حساب فروشگاه متصل نیست."
            );

            return;
        }

        $address = $this->addresses->findById(
            $addressId,
            $userId
        );

        if ($address === null) {
            $this->telegram->sendMessage(
                $chatId,
                "❌ آدرس انتخاب‌شده معتبر نیست."
            );

            return;
        }

        $items = $this->cart->getItems(
            $userId
        );

        if ($items === []) {
            $this->telegram->sendMessage(
                $chatId,
                "🛒 سبد خرید شما خالی است."
            );

            return;
        }

        $total = $this->cart->getTotal(
            $userId
        );

        $text =
            "🧾 تأیید سفارش\n\n";

        $text .= "📍 آدرس ارسال:\n";

        if (
            !empty($address['title'])
        ) {
            $text .= "عنوان: "
                . $address['title']
                . "\n";
        }

        $text .= "👤 گیرنده: "
            . $address['receiver_name']
            . "\n";

        $text .= "📞 تلفن: "
            . $address['phone']
            . "\n";

        $text .= "🏙 "
            . $address['province']
            . " - "
            . $address['city']
            . "\n";

        $text .= "📍 "
            . $address['address']
            . "\n";

        if (
            !empty($address['postal_code'])
        ) {
            $text .= "📮 کد پستی: "
                . $address['postal_code']
                . "\n";
        }

        $text .= "\n";
        $text .= "💰 مبلغ نهایی: "
            . number_format(
                (float) $total,
                0,
                '.',
                ','
            )
            . " تومان\n\n";

        $text .=
            "آیا اطلاعات سفارش صحیح است؟";

        $keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => '✅ تأیید و ثبت سفارش',
                        'callback_data' => "checkout_confirm:{$addressId}",
                    ],
                ],
                [
                    [
                        'text' => '🔙 تغییر آدرس',
                        'callback_data' => 'checkout',
                    ],
                    [
                        'text' => '❌ لغو',
                        'callback_data' => 'checkout_cancel',
                    ],
                ],
            ],
        ];

        $this->telegram->sendMessage(
            $chatId,
            $text,
            $keyboard
        );
    }

    /**
     * Create order from Telegram checkout.
     */
    private function confirmCheckout(
        int|string $chatId,
        int $telegramId,
        int $addressId
    ): void {
        /*
     * ==============================
     * Get linked website user
     * ==============================
     */
        $userId = $this->telegramUsers->getLinkedUserId(
            $telegramId
        );

        if ($userId === null) {
            $this->telegram->sendMessage(
                $chatId,
                "🔐 حساب تلگرام شما به حساب فروشگاه متصل نیست.",
                [
                    'inline_keyboard' => [
                        [
                            [
                                'text' => '🔐 اتصال حساب',
                                'callback_data' => 'account_link',
                            ],
                        ],
                        [
                            [
                                'text' => '🏠 منوی اصلی',
                                'callback_data' => 'main_menu',
                            ],
                        ],
                    ],
                ]
            );

            return;
        }

        /*
     * ==============================
     * Validate address
     * ==============================
     */
        $address = $this->addresses->findById(
            $addressId,
            $userId
        );

        if ($address === null) {
            $this->telegram->sendMessage(
                $chatId,
                "❌ آدرس انتخاب‌شده معتبر نیست.",
                [
                    'inline_keyboard' => [
                        [
                            [
                                'text' => '📍 انتخاب آدرس',
                                'callback_data' => 'checkout',
                            ],
                        ],
                        [
                            [
                                'text' => '🏠 منوی اصلی',
                                'callback_data' => 'main_menu',
                            ],
                        ],
                    ],
                ]
            );

            return;
        }

        /*
     * ==============================
     * Check cart
     * ==============================
     */
        $items = $this->cart->getItems(
            $userId
        );

        if ($items === []) {
            $this->telegram->sendMessage(
                $chatId,
                "🛒 سبد خرید شما خالی است.\n\n"
                    . "ابتدا یک محصول به سبد خرید اضافه کنید.",
                [
                    'inline_keyboard' => [
                        [
                            [
                                'text' => '🔎 جستجوی محصولات',
                                'callback_data' => 'products_search',
                            ],
                        ],
                        [
                            [
                                'text' => '🏠 منوی اصلی',
                                'callback_data' => 'main_menu',
                            ],
                        ],
                    ],
                ]
            );

            return;
        }

        /*
     * ==============================
     * Create order
     * ==============================
     *
     * فقط عملیات ایجاد سفارش داخل
     * این try/catch قرار دارد.
     *
     * اگر سفارش ایجاد شود، خطاهای بعدی
     * نباید باعث نمایش پیام "سفارش ایجاد نشد"
     * شوند.
     */
        try {
            $orderId = $this->orders->createFromCart(
                $userId,
                $addressId
            );
        } catch (Throwable $exception) {
            $message = $exception->getMessage();

            /*
         * ==============================
         * User-friendly errors
         * ==============================
         */
            if (
                str_contains($message, 'خالی')
                || str_contains($message, 'موجودی')
                || str_contains($message, 'فعال')
                || str_contains($message, 'نامعتبر')
            ) {
                $this->telegram->sendMessage(
                    $chatId,
                    "❌ ثبت سفارش انجام نشد.\n\n"
                        . $message,
                    [
                        'inline_keyboard' => [
                            [
                                [
                                    'text' => '🛒 مشاهده سبد',
                                    'callback_data' => 'cart',
                                ],
                            ],
                            [
                                [
                                    'text' => '🏠 منوی اصلی',
                                    'callback_data' => 'main_menu',
                                ],
                            ],
                        ],
                    ]
                );

                return;
            }

            /*
         * ==============================
         * General order creation error
         * ==============================
         */
            $this->telegram->sendMessage(
                $chatId,
                "❌ هنگام ثبت سفارش خطایی رخ داد.\n\n"
                    . "سفارش ایجاد نشد و هیچ پرداختی انجام نشده است.\n\n"
                    . "لطفاً دوباره تلاش کنید.",
                [
                    'inline_keyboard' => [
                        [
                            [
                                'text' => '🛒 بازگشت به سبد',
                                'callback_data' => 'cart',
                            ],
                        ],
                        [
                            [
                                'text' => '🏠 منوی اصلی',
                                'callback_data' => 'main_menu',
                            ],
                        ],
                    ],
                ]
            );

            return;
        }

        /*
     * ==============================
     * Load created order
     * ==============================
     */
        $order = $this->orders->findById(
            $orderId,
            $userId
        );

        if ($order === null) {
            /*
         * سفارش ایجاد شده اما اطلاعات آن
         * قابل بازیابی نیست.
         */
            $this->telegram->sendMessage(
                $chatId,
                "⚠️ سفارش شما ثبت شد، اما دریافت اطلاعات سفارش با مشکل مواجه شد.\n\n"
                    . "🔢 شناسه سفارش: {$orderId}\n\n"
                    . "لطفاً از بخش «سفارش‌های من» سفارش خود را بررسی کنید.",
                [
                    'inline_keyboard' => [
                        [
                            [
                                'text' => '📦 سفارش‌های من',
                                'callback_data' => 'orders',
                            ],
                        ],
                        [
                            [
                                'text' => '🏠 منوی اصلی',
                                'callback_data' => 'main_menu',
                            ],
                        ],
                    ],
                ]
            );

            return;
        }

        /*
     * ==============================
     * Order information
     * ==============================
     */
        $orderNumber = (string) (
            $order['order_number']
            ?? $orderId
        );

        $total = (float) (
            $order['total']
            ?? 0
        );

        /*
     * ==============================
     * Send success message
     * ==============================
     *
     * توجه:
     * در این مرحله هیچ لینک پرداختی
     * ساخته یا ارسال نمی‌شود.
     *
     * کاربر برای ادامه فرآیند خرید و
     * پرداخت به سایت هدایت می‌شود.
     */
        try {
            $this->telegram->sendMessage(
                $chatId,
                "✅ سفارش شما با موفقیت ثبت شد! 🎉\n\n"
                    . "🧾 شماره سفارش:\n"
                    . "{$orderNumber}\n\n"
                    . "💰 مبلغ سفارش:\n"
                    . number_format(
                        $total,
                        0,
                        '.',
                        ','
                    )
                    . " تومان\n\n"
                    . "📦 وضعیت سفارش: در انتظار پرداخت\n"
                    . "💳 وضعیت پرداخت: در انتظار پرداخت\n\n"
                    . "🌐 برای ادامه فرآیند خرید و پرداخت، "
                    . "لطفاً به سایت فروشگاه مراجعه کنید.",
                [
                    'inline_keyboard' => [
                        [
                            [
                                'text' => '📦 سفارش‌های من',
                                'callback_data' => 'orders',
                            ],
                        ],
                        [
                            [
                                'text' => '🏠 منوی اصلی',
                                'callback_data' => 'main_menu',
                            ],
                        ],
                    ],
                ]
            );
        } catch (Throwable $exception) {
            /*
         * سفارش قبلاً ایجاد شده است.
         *
         * بنابراین اگر ارسال پیام با خطا مواجه شد،
         * نباید به کاربر اعلام کنیم که سفارش ایجاد نشده.
         */
            try {
                $this->telegram->sendMessage(
                    $chatId,
                    "✅ سفارش شما با موفقیت ثبت شد! 🎉\n\n"
                        . "🧾 شماره سفارش:\n"
                        . "{$orderNumber}\n\n"
                        . "💰 مبلغ سفارش:\n"
                        . number_format(
                            $total,
                            0,
                            '.',
                            ','
                        )
                        . " تومان\n\n"
                        . "📦 وضعیت سفارش: در انتظار پرداخت\n"
                        . "💳 وضعیت پرداخت: در انتظار پرداخت\n\n"
                        . "🌐 برای ادامه فرآیند خرید و پرداخت، "
                        . "لطفاً به سایت فروشگاه مراجعه کنید.",
                    [
                        'inline_keyboard' => [
                            [
                                [
                                    'text' => '📦 سفارش‌های من',
                                    'callback_data' => 'orders',
                                ],
                            ],
                            [
                                [
                                    'text' => '🏠 منوی اصلی',
                                    'callback_data' => 'main_menu',
                                ],
                            ],
                        ],
                    ]
                );
            } catch (Throwable $ignored) {
                /*
             * در صورت شکست ارسال مجدد پیام،
             * دیگر کاری انجام نمی‌دهیم؛
             * سفارش در دیتابیس ثبت شده است.
             */
            }
        }
    }


    /**
     * Show Telegram user's orders.
     */
    private function showOrders(
        int|string $chatId,
        int $telegramId
    ): void {
        $userId = $this->telegramUsers->getLinkedUserId(
            $telegramId
        );

        if ($userId === null) {
            $this->telegram->sendMessage(
                $chatId,
                "🔐 حساب تلگرام شما هنوز به حساب فروشگاه متصل نیست.\n\n"
                    . "لطفاً ابتدا حساب خود را متصل کنید.",
                [
                    'inline_keyboard' => [
                        [
                            [
                                'text' => '🔐 اتصال حساب',
                                'callback_data' => 'account_link',
                            ],
                        ],
                        [
                            [
                                'text' => '🏠 منوی اصلی',
                                'callback_data' => 'main_menu',
                            ],
                        ],
                    ],
                ]
            );

            return;
        }

        $orders = $this->orders->getUserOrders(
            $userId
        );

        if ($orders === []) {
            $this->telegram->sendMessage(
                $chatId,
                "📦 سفارش‌های من\n\n"
                    . "شما هنوز سفارشی ثبت نکرده‌اید.",
                [
                    'inline_keyboard' => [
                        [
                            [
                                'text' => '🔎 جستجوی محصولات',
                                'callback_data' => 'products_search',
                            ],
                        ],
                        [
                            [
                                'text' => '🏠 منوی اصلی',
                                'callback_data' => 'main_menu',
                            ],
                        ],
                    ],
                ]
            );

            return;
        }

        $text = "📦 سفارش‌های من\n\n";

        $keyboard = [
            'inline_keyboard' => [],
        ];

        foreach ($orders as $order) {
            $orderId = (int) (
                $order['id']
                ?? 0
            );

            if ($orderId <= 0) {
                continue;
            }

            $orderNumber = (string) (
                $order['order_number']
                ?? $orderId
            );

            $total = (float) (
                $order['total']
                ?? 0
            );

            $status = trim(
                (string) (
                    $order['status']
                    ?? ''
                )
            );

            $paymentStatus = trim(
                (string) (
                    $order['payment_status']
                    ?? ''
                )
            );

            $createdAt = trim(
                (string) (
                    $order['created_at']
                    ?? ''
                )
            );

            $text .= "🧾 سفارش #{$orderNumber}\n";

            $text .= "💰 مبلغ: "
                . number_format(
                    $total,
                    0,
                    '.',
                    ','
                )
                . " تومان\n";

            if ($status !== '') {
                $text .= "📦 وضعیت: "
                    . $this->translateOrderStatus(
                        $status
                    )
                    . "\n";
            }

            if ($paymentStatus !== '') {
                $text .= "💳 پرداخت: "
                    . $this->translatePaymentStatus(
                        $paymentStatus
                    )
                    . "\n";
            }

            if ($createdAt !== '') {
                $text .= "📅 تاریخ: {$createdAt}\n";
            }

            $text .= "\n";

            $keyboard['inline_keyboard'][] = [
                [
                    'text' => "👁 مشاهده سفارش #{$orderNumber}",
                    'callback_data' => "order:{$orderId}",
                ],
            ];
        }

        $keyboard['inline_keyboard'][] = [
            [
                'text' => '🔎 ادامه خرید',
                'callback_data' => 'products_search',
            ],
        ];

        $keyboard['inline_keyboard'][] = [
            [
                'text' => '🏠 منوی اصلی',
                'callback_data' => 'main_menu',
            ],
        ];

        $this->telegram->sendMessage(
            $chatId,
            $text,
            $keyboard
        );
    }

    /**
     * Show Telegram user's order details.
     */
    private function showOrder(
        int|string $chatId,
        int $telegramId,
        int $orderId
    ): void {
        $userId = $this->telegramUsers->getLinkedUserId(
            $telegramId
        );

        if ($userId === null) {
            $this->telegram->sendMessage(
                $chatId,
                "🔐 حساب تلگرام شما به حساب فروشگاه متصل نیست.",
                [
                    'inline_keyboard' => [
                        [
                            [
                                'text' => '🔐 اتصال حساب',
                                'callback_data' => 'account_link',
                            ],
                        ],
                        [
                            [
                                'text' => '🏠 منوی اصلی',
                                'callback_data' => 'main_menu',
                            ],
                        ],
                    ],
                ]
            );

            return;
        }

        /*
         * Load order only for the linked user.
         */
        $order = $this->orders->findById(
            $orderId,
            $userId
        );

        if ($order === null) {
            $this->telegram->sendMessage(
                $chatId,
                "❌ سفارش مورد نظر پیدا نشد یا متعلق به حساب شما نیست.",
                [
                    'inline_keyboard' => [
                        [
                            [
                                'text' => '📦 سفارش‌های من',
                                'callback_data' => 'orders',
                            ],
                        ],
                        [
                            [
                                'text' => '🏠 منوی اصلی',
                                'callback_data' => 'main_menu',
                            ],
                        ],
                    ],
                ]
            );

            return;
        }

        $items = $this->orders->getItems(
            $orderId
        );

        $orderNumber = (string) (
            $order['order_number']
            ?? $orderId
        );

        $total = (float) (
            $order['total']
            ?? 0
        );

        $status = trim(
            (string) (
                $order['status']
                ?? ''
            )
        );

        $paymentStatus = trim(
            (string) (
                $order['payment_status']
                ?? ''
            )
        );

        $createdAt = trim(
            (string) (
                $order['created_at']
                ?? ''
            )
        );

        $text =
            "🧾 جزئیات سفارش\n\n";

        $text .= "🔢 شماره سفارش: {$orderNumber}\n";

        if ($createdAt !== '') {
            $text .= "📅 تاریخ ثبت: {$createdAt}\n";
        }

        if ($status !== '') {
            $text .= "📦 وضعیت سفارش: "
                . $this->translateOrderStatus(
                    $status
                )
                . "\n";
        }

        if ($paymentStatus !== '') {
            $text .= "💳 وضعیت پرداخت: "
                . $this->translatePaymentStatus(
                    $paymentStatus
                )
                . "\n";
        }

        $text .= "\n🛍 کالاهای سفارش:\n\n";

        if ($items === []) {
            $text .= "اطلاعات کالاهای سفارش موجود نیست.\n";
        } else {
            foreach ($items as $item) {
                $name = (string) (
                    $item['product_name']
                    ?? 'محصول'
                );

                $quantity = (int) (
                    $item['quantity']
                    ?? 0
                );

                $unitPrice = (float) (
                    $item['price']
                    ?? 0
                );

                $itemTotal = (float) (
                    $item['total']
                    ?? 0
                );

                $text .= "🎧 {$name}\n";
                $text .= "🔢 تعداد: {$quantity}\n";
                $text .= "💰 قیمت واحد: "
                    . number_format(
                        $unitPrice,
                        0,
                        '.',
                        ','
                    )
                    . " تومان\n";
                $text .= "💵 مجموع: "
                    . number_format(
                        $itemTotal,
                        0,
                        '.',
                        ','
                    )
                    . " تومان\n\n";
            }
        }

        $text .= "━━━━━━━━━━━━━━\n";

        $text .= "💰 مبلغ نهایی: "
            . number_format(
                $total,
                0,
                '.',
                ','
            )
            . " تومان";

        $keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => '🔙 سفارش‌های من',
                        'callback_data' => 'orders',
                    ],
                ],
                [
                    [
                        'text' => '🏠 منوی اصلی',
                        'callback_data' => 'main_menu',
                    ],
                ],
            ],
        ];

        $this->telegram->sendMessage(
            $chatId,
            $text,
            $keyboard
        );
    }

    /**
     * Translate order status.
     */
    private function translateOrderStatus(
        string $status
    ): string {
        return match ($status) {
            'pending' => '⏳ در انتظار پرداخت',
            'paid' => '💳 پرداخت شده',
            'processing' => '⚙️ در حال پردازش',
            'shipped' => '🚚 ارسال شده',
            'delivered' => '✅ تحویل داده شده',
            'cancelled' => '❌ لغو شده',
            'failed' => '⚠️ ناموفق',
            default => $status,
        };
    }

    /**
     * Translate payment status.
     */
    private function translatePaymentStatus(
        string $status
    ): string {
        return match ($status) {
            'pending' => '⏳ در انتظار پرداخت',
            'success' => '✅ پرداخت شده',
            'paid' => '✅ پرداخت شده',
            'failed' => '❌ ناموفق',
            'refunded' => '↩️ برگشت وجه',
            default => $status,
        };
    }

    /**
     * Format product price.
     */
    private function formatPrice(
        array $product
    ): string {
        $discountPrice =
            $product['discount_price']
            ?? null;

        $price = $discountPrice !== null
            && $discountPrice !== ''
            ? (float) $discountPrice
            : (float) (
                $product['price']
                ?? 0
            );

        return number_format(
            $price,
            0,
            '.',
            ','
        ) . ' تومان';
    }
}
