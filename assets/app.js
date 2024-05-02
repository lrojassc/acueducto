import './styles/app.scss';
import 'bootstrap';
import 'datatables.net';
import 'datatables.net-dt';

$('#datatable-users').DataTable({
    language: {
        url: '//cdn.datatables.net/plug-ins/2.0.5/i18n/es-ES.json',
    }
});
