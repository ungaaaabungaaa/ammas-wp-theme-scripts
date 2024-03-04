# ammas-wp-theme-scripts
This repository contains custom PHP scripts designed to enhance the functionality of a WordPress website using the Astra theme, tailored specifically for Ammas Pastries. These scripts add various features and restrictions to the WooCommerce setup of the website.

- **Features Included:**
  1. **Restrict Cash On Delivery Orders**
     - Limits Cash On Delivery (COD) orders to a maximum of 150 AED for UAE customers.

  2. **Hide Cash On Delivery and Telr for India**
     - Hides the COD and Telr payment options for customers in India.

  3. **Disable Unique SKUs**
     - Disables the requirement for unique SKUs for all products.

  4. **Hide Razorpay Payment Gateway for UAE**
     - Removes the Razorpay payment option for customers in the United Arab Emirates.

  5. **Restrict Previous Date Selection**
     - Allows only future dates to be selected in the date picker on the checkout page.

  6. **Disable Webhook**
     - Disables a webhook by its ID in the WooCommerce API Keys table.

  7. **Display Only Three States**
     - Limits the states displayed at checkout to Karnataka, Kerala, and Andhra Pradesh for Indian customers.

  8. **Trigger Webhook After Payment Completion**
     - Triggers a webhook after payment completion, based on shipping country and payment status.

  9. **Remove Bloat Notices**
     - Keeps only error notices on the checkout page and removes other notices.

  10. **Register New Post Types for Order Statuses**
      - Registers custom order statuses ('Fulfilled' and 'Unfulfilled') and adds them to the order status dropdown.

  11. **Webhook Callback Endpoint**
      - Registers a custom endpoint to handle webhook callbacks for updating order statuses.

- **Installation and Usage:**
  1. **Download:**
     - Download the PHP scripts provided in this repository.

  2. **Integration:**
     - Integrate the scripts into your WordPress project, specifically within the theme's functions.php file or via a custom plugin.

  3. **Configuration:**
     - Customize the script parameters such as maximum order totals, countries, payment gateway IDs, etc., according to your specific 
requirements.

  4. **Testing:**
     - Test thoroughly to ensure that the implemented functionality works as expected across different scenarios and customer interactions.

- **Note:**
  - These scripts are tailored for the specific needs of Ammas Pastries and may require adjustments for compatibility with other themes, plugins, or unique business requirements.

- **Support:**
  - For any inquiries or support requests, please contact me 
