/**
 * The Birthday Wishbook — Dashboard JavaScript
 * Handles countdown, CRUD operations, copy link, image preview, toasts, and confetti
 */

// ─── Local SVG Icons ───
const svgIcons = {
    check: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon"><circle cx="12" cy="12" r="10"></circle><path d="m9 12 2 2 4-4"></path></svg>`,
    warning: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon"><path d="m10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>`,
    error: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>`,
    info: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>`,
    gift: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon"><rect x="3" y="8" width="18" height="4" rx="1"></rect><path d="M12 8v13"></path><path d="M19 12v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7"></path><path d="M7.5 8a2.5 2.5 0 0 1 0-5A2.5 2.5 0 0 1 12 8a2.5 2.5 0 0 1 4.5-5a2.5 2.5 0 0 1 0 5"></path></svg>`,
    clipboard: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>`,
    image: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>`,
    edit: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>`,
    popper: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon"><path d="M18 3 6 15"></path><path d="m16 8 3-3"></path><path d="m12 12 3-3"></path><path d="m10 5 1 2"></path><path d="M21 9h-2"></path><path d="M18 13v-2"></path><path d="M9 22H3v-6Z"></path></svg>`,
    sparkles: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"></path></svg>`
};

// ─── CSRF Token ───
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

// ─── Real-Time Countdown ───
function initCountdown() {
    const grid = document.getElementById('countdownGrid');
    if (!grid) return; // Birthday is today, no countdown
    
    const targetTimestamp = parseInt(grid.dataset.target);
    
    function updateCountdown() {
        const now = Date.now();
        const diff = targetTimestamp - now;
        
        if (diff <= 0) {
            // Birthday has arrived — reload the page for confetti!
            window.location.reload();
            return;
        }
        
        const days    = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours   = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);
        
        const daysEl = document.getElementById('countdown-days');
        const hoursEl = document.getElementById('countdown-hours');
        const minutesEl = document.getElementById('countdown-minutes');
        const secondsEl = document.getElementById('countdown-seconds');
        
        if (daysEl) daysEl.textContent = days;
        if (hoursEl) hoursEl.textContent = hours;
        if (minutesEl) minutesEl.textContent = minutes;
        if (secondsEl) secondsEl.textContent = seconds;
    }
    
    updateCountdown();
    setInterval(updateCountdown, 1000);
}

// ─── Copy Share Link ───
function copyShareLink() {
    const input = document.getElementById('shareLink');
    const btn = document.getElementById('copyLinkBtn');
    if (!input) return;
    
    const url = input.value;
    
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(() => {
            btn.innerHTML = `${svgIcons.check} Copied!`;
            showToast('Link copied to clipboard!', 'success');
            setTimeout(() => { btn.innerHTML = `${svgIcons.clipboard} Copy`; }, 2000);
        }).catch(() => {
            fallbackCopy(input, btn);
        });
    } else {
        fallbackCopy(input, btn);
    }
}

function fallbackCopy(input, btn) {
    input.select();
    input.setSelectionRange(0, 99999);
    document.execCommand('copy');
    btn.innerHTML = `${svgIcons.check} Copied!`;
    showToast('Link copied to clipboard!', 'success');
    setTimeout(() => { btn.innerHTML = `${svgIcons.clipboard} Copy`; }, 2000);
}

// ─── Image Preview ───
function previewImage(url) {
    const previewBox = document.getElementById('imagePreview');
    if (!previewBox) return;
    
    if (!url || !url.match(/^https?:\/\/.+/)) {
        previewBox.innerHTML = `
            <div class="image-preview-placeholder">
                <span>${svgIcons.image}</span>
                <p>Image preview will appear here</p>
            </div>`;
        previewBox.classList.remove('has-image');
        return;
    }
    
    const img = new Image();
    img.onload = function() {
        previewBox.innerHTML = '';
        previewBox.appendChild(img);
        previewBox.classList.add('has-image');
    };
    img.onerror = function() {
        previewBox.innerHTML = `
            <div class="image-preview-placeholder">
                <span>${svgIcons.warning}</span>
                <p>Could not load image — the URL may be invalid or blocked</p>
            </div>`;
        previewBox.classList.remove('has-image');
    };
    img.src = url;
    img.alt = 'Preview';
}

// ─── Modal Management ───
function openAddModal() {
    const modal = document.getElementById('itemModal');
    const form = document.getElementById('itemForm');
    const title = document.getElementById('modalTitle');
    const submitBtn = document.getElementById('modalSubmitBtn');
    
    form.reset();
    document.getElementById('itemId').value = '';
    title.innerHTML = `${svgIcons.gift} Add New Wish`;
    submitBtn.innerHTML = `Add to Wishlist ${svgIcons.popper}`;
    
    // Reset category fields
    const catSelect = document.getElementById('itemCategory');
    if (catSelect) {
        catSelect.value = '';
    }
    const newCatGroup = document.getElementById('newCategoryGroup');
    if (newCatGroup) {
        newCatGroup.style.display = 'none';
    }
    const newCatInput = document.getElementById('newCategoryName');
    if (newCatInput) {
        newCatInput.required = false;
        newCatInput.value = '';
    }
    
    // Reset image preview
    previewImage('');
    
    modal.classList.add('active');
    document.getElementById('itemName').focus();
}

// Ensure edit modal shows proper SVGs
function openEditModal(item) {
    const modal = document.getElementById('itemModal');
    const title = document.getElementById('modalTitle');
    const submitBtn = document.getElementById('modalSubmitBtn');
    
    document.getElementById('itemId').value = item.id;
    document.getElementById('itemName').value = item.item_name || '';
    document.getElementById('itemUrl').value = item.item_url || '';
    document.getElementById('imageUrl').value = item.image_url || '';
    
    // Reset and select category
    const catSelect = document.getElementById('itemCategory');
    if (catSelect) {
        catSelect.value = item.category_id || '';
    }
    const newCatGroup = document.getElementById('newCategoryGroup');
    if (newCatGroup) {
        newCatGroup.style.display = 'none';
    }
    const newCatInput = document.getElementById('newCategoryName');
    if (newCatInput) {
        newCatInput.required = false;
        newCatInput.value = '';
    }
    
    title.innerHTML = `${svgIcons.edit} Edit Wish`;
    submitBtn.innerHTML = `Save Changes ${svgIcons.sparkles}`;
    
    // Show image preview if exists
    previewImage(item.image_url || '');
    
    modal.classList.add('active');
    document.getElementById('itemName').focus();
}

function closeModal() {
    document.getElementById('itemModal').classList.remove('active');
}

// Close modal on overlay click
document.getElementById('itemModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});

// ─── CRUD Operations ───
async function handleItemSubmit(event) {
    event.preventDefault();
    
    const form = event.target;
    const itemId = document.getElementById('itemId').value;
    const isEdit = !!itemId;
    
    const formData = new FormData(form);
    formData.append('csrf_token', csrfToken);
    
    const endpoint = isEdit ? 'api/edit-item.php' : 'api/add-item.php';
    
    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            body: formData,
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast(data.message, 'success');
            closeModal();
            
            // Reload page to reflect changes (simpler and more reliable)
            setTimeout(() => window.location.reload(), 500);
        } else {
            showToast(data.message || 'Something went wrong.', 'error');
        }
    } catch (err) {
        showToast('Network error. Please try again.', 'error');
        console.error('Submit error:', err);
    }
}

let itemToDelete = null;

function deleteItem(itemId) {
    itemToDelete = itemId;
    const modal = document.getElementById('confirmModal');
    if (modal) {
        modal.classList.add('active');
    }
}

function closeConfirmModal() {
    const modal = document.getElementById('confirmModal');
    if (modal) {
        modal.classList.remove('active');
    }
    itemToDelete = null;
}

async function executeDelete() {
    if (!itemToDelete) return;
    const itemId = itemToDelete;
    closeConfirmModal();
    
    const formData = new FormData();
    formData.append('item_id', itemId);
    formData.append('csrf_token', csrfToken);
    
    try {
        const response = await fetch('api/delete-item.php', {
            method: 'POST',
            body: formData,
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast(data.message, 'success');
            
            // Remove card with animation
            const card = document.getElementById(`wish-${itemId}`);
            if (card) {
                card.style.transition = 'all 0.3s ease';
                card.style.transform = 'scale(0.8)';
                card.style.opacity = '0';
                setTimeout(() => {
                    card.remove();
                    updateItemCount();
                }, 300);
            }
        } else {
            showToast(data.message || 'Could not delete item.', 'error');
        }
    } catch (err) {
        showToast('Network error. Please try again.', 'error');
        console.error('Delete error:', err);
    }
}

function updateItemCount() {
    const grid = document.getElementById('wishlistGrid');
    const countBadge = document.getElementById('itemCount');
    if (!grid || !countBadge) return;
    
    const cards = grid.querySelectorAll('.wish-card');
    const count = cards.length;
    countBadge.textContent = `${count} item${count !== 1 ? 's' : ''}`;
    
    // Show empty state if no items
    if (count === 0) {
        grid.innerHTML = `
            <div class="empty-state" id="emptyState" style="grid-column: 1 / -1;">
                <span class="empty-state-emoji">${svgIcons.gift}</span>
                <h3>Your wishlist is empty!</h3>
                <p>Start adding items you'd love to receive for your birthday.</p>
                <button class="btn btn-primary" style="margin-top: var(--space-md);" onclick="openAddModal()">+ Add Your First Wish</button>
            </div>`;
    }
}

// ─── Confetti (for Birthday!) ───
function launchConfetti() {
    const canvas = document.getElementById('confetti-canvas');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
    
    const colors = ['#FFB5C2', '#D4B5FF', '#B5F0D4', '#FFE566', '#FFCBA4', '#A4D8FF'];
    const confetti = [];
    
    for (let i = 0; i < 150; i++) {
        confetti.push({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height - canvas.height,
            w: Math.random() * 10 + 5,
            h: Math.random() * 6 + 3,
            color: colors[Math.floor(Math.random() * colors.length)],
            speed: Math.random() * 3 + 2,
            angle: Math.random() * 360,
            spin: (Math.random() - 0.5) * 8,
            drift: (Math.random() - 0.5) * 2,
        });
    }
    
    let frameCount = 0;
    
    function animate() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        confetti.forEach(c => {
            ctx.save();
            ctx.translate(c.x + c.w / 2, c.y + c.h / 2);
            ctx.rotate((c.angle * Math.PI) / 180);
            ctx.fillStyle = c.color;
            ctx.fillRect(-c.w / 2, -c.h / 2, c.w, c.h);
            ctx.restore();
            
            c.y += c.speed;
            c.x += c.drift;
            c.angle += c.spin;
            
            if (c.y > canvas.height) {
                c.y = -20;
                c.x = Math.random() * canvas.width;
            }
        });
        
        frameCount++;
        if (frameCount < 600) { // ~10 seconds
            requestAnimationFrame(animate);
        } else {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            canvas.remove();
        }
    }
    
    animate();
    
    // Handle window resize
    window.addEventListener('resize', () => {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    });
}

// ─── Initialize ───
document.addEventListener('DOMContentLoaded', function() {
    initCountdown();
    
    // Launch confetti if birthday
    if (document.getElementById('confetti-canvas')) {
        launchConfetti();
    }
    
    // Bind custom delete confirmation trigger
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', executeDelete);
    }
});
