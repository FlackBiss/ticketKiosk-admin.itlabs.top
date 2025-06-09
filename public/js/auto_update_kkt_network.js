function updateTerminalsStatus() {
    fetch('/api/terminals-ping', {headers: {'X-API-KEY': '16777761'}})
        .then(response => response.json())
        .then(pingData => {
            const pingMap = {};
            pingData.forEach(t => {
                pingMap[t.id] = t.online;
            });

            console.log(pingData);

            fetch('/api/terminals', {headers: {'X-API-KEY': '16777761'}})
                .then(response => response.json())
                .then(terminals => {
                    terminals.forEach(terminal => {
                        const row = document.querySelector(`tr[data-id="${terminal.id}"]`);
                        if (!row) return;

                        const networkCell = row.querySelector('td[data-column="isNetworkStringify"] span > div');
                        if (networkCell && terminal.id in pingMap) {
                            if (pingMap[terminal.id]) {
                                networkCell.innerText = 'Да';
                                networkCell.style.color = 'green';
                            } else {
                                networkCell.innerText = 'Нет';
                                networkCell.style.color = 'red';
                            }
                        }

                        const kktCell = row.querySelector('td[data-column="isKktStringify"] span > div');
                        if (kktCell) {
                            if (terminal.kkt) {
                                kktCell.innerText = 'Да';
                                kktCell.style.color = 'green';
                            } else {
                                kktCell.innerText = 'Нет';
                                kktCell.style.color = 'red';
                            }
                        }
                    });
                });
        });
}

setInterval(updateTerminalsStatus, 10000);
updateTerminalsStatus();
