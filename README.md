# NativeLang WordPress Plugin

**Empowering Multilingual WordPress Sites with Advanced Translation Capabilities**

NativeLang is a WordPress plugin designed to significantly enhance your website's multilingual capabilities. Built as an extension for the popular Polylang plugin, NativeLang adds advanced translation features that make it easier than ever to manage and deliver content in multiple languages. Its purpose is to offer WordPress users a fast, intuitive, and flexible solution for multilingual content management, while allowing developers and website owners to customize or integrate it into their own WordPress sites.

### 🚀 Key Features

-   **Enhanced Translation Management:** Streamline the process of translating your content, making it easier and more efficient.
-   **Seamless Integration with Polylang:** Extends the functionality of Polylang without disrupting existing workflows. It works as a natural extension, enhancing Polylang's core capabilities.
-   **Multisite Support:** Fully compatible with WordPress Multisite setups, including subdomain configurations. Manage multiple sites with different languages efficiently.
-   **Customizable Translations:** Offers flexibility to tailor translations according to specific needs. Adapt translations to your specific audience and context.
-   **User-Friendly Interface:** Integration with WordPress dashboard is seamless and transparent. There are no extra user interface to manage. Just use the WordPress dashboard.
-   **Security Features:** Includes an optional **Kill Switch** (to block internet if the connection drops unexpectedly) and **Auto-Connect** (to automatically connect on app launch or network change). These ensure continuous protection without manual intervention.

### 📦 Architecture Overview

NativeLang is architected in layers to separate the user interface from core translation logic:

-   **WordPress UI (PHP):** Implements all screens and user interactions (content creation, translation, etc.) via the WordPress dashboard. This layer handles all input/output with the user.
-   **NativeLang Plugin Core (PHP):** The underlying PHP core that handles translation functionality. It manages connections with Polylang and translation configurations. The engine integrates multiple implementations and configurations under a common API.

