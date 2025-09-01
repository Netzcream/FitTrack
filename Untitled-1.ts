(async () => {
    const startPage = 201;   // Desde página
    const endPage = 471;    // Hasta página
    const delayMs = 1500;   // Delay en ms (1000 = 1s)
    const batchSize = 10;   // Cada cuanto guarda el CSV (ej: 10 páginas)
    
    const headers = [
        'Nombre', 'Mail', 'CUIT', 'Ciudad', 'Razon Social', 'Legajo',
        'Markup', 'MarkupT', 'Iata', 'Codigo Postal', 'Fax', 'Prospect',
        'Mail Administrativo', 'Netviax', 'Netviax mblanca', 'Estado de emision',
        'Cuenta Corriente', 'Comentario Interno', 'Creador', 'Nro Aptour',
        'Direccion', 'Telefono', 'Fecha de creacion', 'Fecha ultima modificacion'
    ];

    function escapeCsvCell(cell) {
        if (cell == null) cell = '';
        cell = cell.toString().replace(/"/g, '""');
        return `"${cell}"`;
    }

    const sleep = ms => new Promise(resolve => setTimeout(resolve, ms));
    const parser = new DOMParser();

    let batchResults = [headers];
    let batchFirstPage = startPage;

    async function saveBatch(first, last, rows) {
        // Generar y descargar CSV
        let csv = rows.map(row => row.map(escapeCsvCell).join(';')).join('\n');
        let blob = new Blob([csv], { type: 'text/csv;charset=utf-8' });
        let filename = `agencias_${first}-${last}.csv`;
        let link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = filename;
        link.click();
        console.log(`Descargado: ${filename}`);
    }

    try {
        for (let page = startPage; page <= endPage; page++) {
            console.log(`Procesando página ${page}...`);
            let url = `/agencies?page=${page}`;
            let response = await fetch(url, { credentials: 'include' });
            let text = await response.text();
            let doc = parser.parseFromString(text, 'text/html');
            let rows = Array.from(doc.querySelectorAll('table tbody tr'));

            for (let tr of rows) {
                try {
                    let linkTag = tr.querySelector('a.show-in-modal, a[href*="/agencies/"]');
                    if (!linkTag) continue;
                    let detalleUrl = linkTag.getAttribute('href');
                    if (!detalleUrl.startsWith('/')) detalleUrl = '/' + detalleUrl;

                    console.log(`→ Consultando detalle: ${detalleUrl}`);
                    let respDet = await fetch(detalleUrl, { credentials: 'include' });
                    let htmlDet = await respDet.text();
                    let detDoc = parser.parseFromString(htmlDet, 'text/html');

                    function getValor(label) {
                        let el = Array.from(detDoc.querySelectorAll('label.control-label')).find(
                            l => l.textContent.trim().replace(/\s*:/, '') === label
                        );
                        if (!el) return '';
                        let val = el.parentElement.textContent.replace(label + ' :', '').trim();
                        return val.replace(/[\r\n\t]+/g, ' ').replace(/\s\s+/g, ' ');
                    }

                    let row = [
                        getValor('Nombre'),
                        getValor('Mail'),
                        getValor('Cuit'),
                        getValor('Ciudad'),
                        getValor('Razon Social'),
                        getValor('Legajo'),
                        getValor('Markup'),
                        getValor('MarkupT'),
                        getValor('Iata'),
                        getValor('Codigo Postal'),
                        getValor('Fax'),
                        getValor('Prospect'),
                        getValor('Mail Administrativo'),
                        getValor('Netviax'),
                        getValor('Netviax mblanca'),
                        getValor('Estado de emision'),
                        getValor('Cuenta Corriente'),
                        getValor('Comentario Interno'),
                        getValor('Creador'),
                        getValor('Nro Aptour'),
                        getValor('Direccion'),
                        getValor('Telefono'),
                        getValor('Fecha de creacion'),
                        getValor('Fecha ultima modificacion')
                    ];
                    batchResults.push(row);
                } catch (err) {
                    console.error('Error en fetch detalle fila, se saltea fila. Motivo:', err);
                    batchResults.push(Array(headers.length).fill('ERROR'));
                }
                await sleep(delayMs);
            }

            // ¿Es momento de guardar batch?
            let isLastPage = (page === endPage);
            let reachedBatch = ((page - batchFirstPage + 1) === batchSize);

            if (reachedBatch || isLastPage) {
                let batchLastPage = page;
                await saveBatch(batchFirstPage, batchLastPage, batchResults);
                // Prepara para el próximo batch
                batchResults = [headers];
                batchFirstPage = page + 1;
            }
        }
    } catch (err) {
        alert('¡Ocurrió un error global! Se descargará el batch en curso.');
        let batchLastPage = batchFirstPage + batchResults.length - 2; // -header, -1 porque index
        await saveBatch(batchFirstPage, batchLastPage, batchResults);
    }
    alert('¡Proceso finalizado!');
})();
