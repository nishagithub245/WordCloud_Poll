    </div>
    
    <script src="script.js"></script>
    <script>
        // Global configuration
        const API_BASE_URL = 'api/';
        
        // Show loading overlay
        function showLoading(show) {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) {
                overlay.style.display = show ? 'flex' : 'none';
            }
        }
        
        // Show error message
        function showError(message, elementId = 'errorMessage') {
            const el = document.getElementById(elementId);
            if (el) {
                el.textContent = message;
                el.style.display = 'block';
                setTimeout(() => el.style.display = 'none', 5000);
            } else {
                alert(message);
            }
        }
        
        // Show success message
        function showSuccess(message, elementId = 'successMessage') {
            const el = document.getElementById(elementId);
            if (el) {
                el.textContent = message;
                el.style.display = 'block';
                setTimeout(() => el.style.display = 'none', 3000);
            }
        }
        
        // Copy to clipboard
        function copyToClipboard(elementId) {
            const input = document.getElementById(elementId);
            input.select();
            input.setSelectionRange(0, 99999);
            
            try {
                document.execCommand('copy');
                const button = event.target.closest('button') || event.target;
                const original = button.innerHTML;
                button.innerHTML = 'Copied!';
                setTimeout(() => button.innerHTML = original, 2000);
            } catch(err) {
                showError('Failed to copy to clipboard');
            }
        }
    </script>
</body>
</html>