// script.js - Main JavaScript functionality
document.addEventListener('DOMContentLoaded', () => {
    const q = document.getElementById('q');
    const tableArea = document.getElementById('table-area');
    const editModal = document.getElementById('edit-modal');
    const editForm = document.getElementById('edit-form');
    const closeModal = document.getElementById('close-modal');

    // Debounce search input
    let timer = null;
    q.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(() => performSearch(q.value), 300);
    });

    // Close modal
    closeModal.addEventListener('click', () => {
        editModal.style.display = 'none';
    });

    // Handle edit form submission
    editForm.addEventListener('submit', (e) => {
        e.preventDefault();
        editForm.submit();
    });

    /**
     * Perform search with API call
     */
    function performSearch(query) {
        if (!query.trim()) {
            location.reload();
            return;
        }

        fetch('cari.php?q=' + encodeURIComponent(query))
            .then(r => r.json())
            .then(data => renderTable(data))
            .catch(e => console.error('Search error:', e));
    }

    /**
     * Render table with transaction data
     */
    function renderTable(data) {
        let html = '<table>';
        html += '<thead><tr><th>ID</th><th>Nama</th><th>Berat</th><th>Total</th><th>Status</th><th>Aksi</th></tr></thead>';
        html += '<tbody>';

        data.forEach(row => {
            html += `
                <tr>
                    <td>${escapeHtml(row.id)}</td>
                    <td>${escapeHtml(row.nama)}</td>
                    <td>${escapeHtml(row.berat)} kg</td>
                    <td>Rp ${formatNumber(row.total)}</td>
                    <td>${escapeHtml(row.status)}</td>
                    <td>
                        <button class="btn-edit" data-id="${row.id}">Edit</button>
                        <form method="post" class="inline-form" onsubmit="return confirm('Hapus transaksi ini?')">
                            <input type="hidden" name="action" value="hapus">
                            <input type="hidden" name="id" value="${row.id}">
                            <button type="submit" class="btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            `;
        });

        html += '</tbody></table>';

        // Replace only table content, keep export button
        const oldTable = tableArea.querySelector('table');
        if (oldTable) oldTable.remove();

        const heading = tableArea.querySelector('h2');
        heading.insertAdjacentHTML('afterend', html);

        attachEditButtons();
    }

    /**
     * Attach click listeners to edit buttons
     */
    function attachEditButtons() {
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', function() {
                const tr = this.closest('tr');
                const id = this.getAttribute('data-id');
                const nama = tr.children[1].innerText;
                const berat = tr.children[2].innerText.replace(' kg', '');
                const status = tr.children[4].innerText;

                document.getElementById('e-id').value = id;
                document.getElementById('e-nama').value = nama;
                document.getElementById('e-berat').value = berat;
                document.getElementById('e-status').value = status;

                editModal.style.display = 'flex';
            });
        });
    }

    /**
     * Escape HTML special characters
     */
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    /**
     * Format number with dot separator
     */
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    // Initial attachment of edit buttons
    attachEditButtons();
});
