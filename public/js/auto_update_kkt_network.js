function updateTerminalsStatus() {
    fetch('/api/terminals-status', { headers: { 'X-API-KEY': '16777761' } })
        .then(response => response.json())
        .then(terminals => {
            terminals.forEach(terminal => {
                const row = document.querySelector(`tr[data-id="${terminal.id}"]`);
                if (!row) return;

                const networkCell = row.querySelector('td[data-column="isNetworkStringify"] span > div');
                if (networkCell) {
                    networkCell.innerText = terminal.online ? 'Да' : 'Нет';
                    networkCell.style.color = terminal.online ? 'green' : 'red';
                }

                const kktCell = row.querySelector('td[data-column="isKktStringify"] span > div');
                if (kktCell) {
                    kktCell.innerText = terminal.kkt ? 'Да' : 'Нет';
                    kktCell.style.color = terminal.kkt ? 'green' : 'red';
                }
            });
        });
}

setInterval(updateTerminalsStatus, 10000);
updateTerminalsStatus();
