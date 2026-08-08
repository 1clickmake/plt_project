<section class="contact-section">
    <div class="form-container">
        <div class="form-header">
            <h2>Contact Us</h2>
            <p>Do you have any questions? Please feel free to contact us directly.</p>
        </div>

        <form id="contact-form" onsubmit="submitContactForm(event)">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="target_info" value="contact">
            
            <div class="form-row">
                <div class="form-group">
                    <label>Your Name</label>
                    <input type="text" name="name" required placeholder="John Doe">
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" placeholder="010-0000-0000">
                </div>
                <div class="form-group">
                    <label>Your Email</label>
                    <input type="email" name="email" required placeholder="john@example.com">
                </div>
            </div>
            
            <div class="form-group">
                <label>Subject</label>
                <input type="text" name="subject" required placeholder="How can we help you?">
            </div>

            <div class="form-group message-group">
                <label>Message</label>
                <textarea name="message" rows="5" required placeholder="Enter your message here..."></textarea>
            </div>

            <div class="form-footer">
                <button type="submit" id="btn-submit-contact">
                    <i class="fa-solid fa-paper-plane"></i> Send Message
                </button>
            </div>
        </form>
    </div>
</section>

