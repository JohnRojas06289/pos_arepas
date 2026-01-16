// Simple-DataTables
// https://github.com/fiduswriter/Simple-DataTables/wiki

window.addEventListener('DOMContentLoaded', event => {
    const datatablesSimple = document.getElementById('datatablesSimple');
    if (datatablesSimple) {
        new simpleDatatables.DataTable(datatablesSimple, {
            labels: {
                placeholder: "Buscar...",
                perPage: "Registros por página:",
                noRows: "No se encontraron registros",
                info: "Mostrando {start} a {end} de {rows} registros",
                noResults: "No se encontraron resultados para tu búsqueda",
            }
        });
    }
});
