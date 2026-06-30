    <!-- Footer -->
    <footer class="site-footer">
        <p>MADE BY <a href="https://www.alvinsonny.me" target="_blank" rel="noopener noreferrer">ALVIN</a></p>
    </footer>
    
    </div><!-- /.page-wrapper -->
    
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>
    
    <!-- Global Scripts -->
    <script>
        // Mobile Nav Toggle
        document.getElementById('navToggle')?.addEventListener('click', function() {
            document.getElementById('navMenu').classList.toggle('active');
        });

        // Global Toast Notification Helper
        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            if (!container) return;
            
            const icons = {
                success: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon"><circle cx="12" cy="12" r="10"></circle><path d="m9 12 2 2 4-4"></path></svg>`,
                error: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>`,
                info: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>`
            };
            
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.innerHTML = `<span>${icons[type] || icons.info}</span> ${escapeHtml(message)}`;
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
    
    <?php if ($flash): ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            showToast(<?php echo json_encode($flash['message']); ?>, <?php echo json_encode($flash['type'] === 'error' ? 'error' : 'success'); ?>);
        });
    </script>
    <?php endif; ?>
    
    <?php if (isset($pageScripts)): ?>
        <?php foreach ($pageScripts as $script): ?>
            <script src="<?php echo getBaseUrl(); ?>/assets/js/<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
