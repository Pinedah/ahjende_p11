<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Práctica 10 - Árbol Recursivo de Ejecutivos</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    
    <!-- jsTree CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.15/themes/default/style.min.css">
    
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jsTree JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.15/jstree.min.js"></script>
    
    <style>
        /* Estilos para mantener sinergia con index.php */
        .horario-column {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
        }
        
        .filter-section {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .search-section {
            background-color: #f1f3f4;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        /* Contenedores principales */
        .tree-container {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            min-height: 500px;
        }
        
        .actions-panel {
            background-color: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
        }
        
        .info-panel {
            background-color: #e3f2fd;
            border: 1px solid #90caf9;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        /* Panel de estadísticas similar a cards en index.php */
        .stats-container {
            background-color: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        /* Estilos del árbol jsTree */
        #jstree {
            background-color: white;
            border-radius: 5px;
            padding: 15px;
            min-height: 400px;
            border: 1px solid #dee2e6;
        }
        
        .btn-action {
            margin: 5px;
            min-width: 120px;
        }
        
        .status-badge {
            font-size: 0.8em;
            margin-left: 5px;
        }
        
        .node-info {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-top: 10px;
            border-left: 4px solid #007bff;
        }
        
        .breadcrumb-custom {
            background-color: #e9ecef;
            border-radius: 5px;
            padding: 8px 15px;
            margin-bottom: 15px;
        }
        
        /* Estilos para drag & drop mejorados */
        .jstree-dnd-helper {
            background: #007bff !important;
            color: white !important;
            border-radius: 3px !important;
            padding: 5px 10px !important;
            font-weight: bold !important;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3) !important;
        }
        
        .jstree-dnd-helper .jstree-icon {
            color: white !important;
        }
        
        /* Indicador visual para drop zones */
        .jstree-drop {
            background-color: rgba(0, 123, 255, 0.15) !important;
            border: 2px dashed #007bff !important;
            border-radius: 3px !important;
        }
        
        /* Estilos para nodos siendo arrastrados */
        .jstree-dragged {
            opacity: 0.6 !important;
        }
        
        /* Mejores estilos para los badges similar a index.php */
        .badge {
            font-size: 0.8em;
            padding: 0.4em 0.6em;
        }
        
        /* Estilos para modales similar a index.php */
        .modal-header {
            background-color: #007bff;
            color: white;
        }
        
        .modal-header .close {
            color: white;
            opacity: 0.8;
        }
        
        .modal-header .close:hover {
            opacity: 1;
        }
        
        /* Mensaje de estado para drag & drop */
        .drag-status {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            z-index: 9999;
            display: none;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }
        
        .drag-status.error {
            background: #dc3545;
        }
        
        .drag-status.success {
            background: #28a745;
        }
        
        /* Estilos para mejorar jsTree similar a index.php */
        .jstree-default .jstree-node {
            min-height: 30px;
            line-height: 30px;
            margin-left: 0px;
            min-width: 24px;
        }
        
        .jstree-default .jstree-anchor {
            line-height: 30px;
            height: 30px;
            padding: 0 4px 0 1px;
        }
        
        .jstree-default .jstree-icon {
            width: 18px;
            height: 18px;
            line-height: 18px;
            margin-top: 6px;
        }
        
        /* Estilos para el header similar a index.php */
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        
        .card-header h4 {
            margin-bottom: 5px;
            color: #495057;
        }
        
        .card-header small {
            color: #6c757d;
        }
        
        /* Botones de acción con colores consistentes */
        .btn-outline-primary:hover,
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        
        .btn-outline-secondary:hover,
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        
        .btn-outline-success:hover,
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }
        
        .btn-outline-warning:hover,
        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #212529;
        }
        
        .btn-outline-info:hover,
        .btn-info {
            background-color: #17a2b8;
            border-color: #17a2b8;
        }
        
        /* Estilos adicionales para mejorar la sangría del árbol */
        .jstree-default .jstree-children {
            margin-left: 40px;
            position: relative;
        }
        
        /* Línea vertical principal */
        .jstree-default .jstree-children:before {
            content: '';
            position: absolute;
            left: -25px;
            top: 0;
            bottom: 18px;
            width: 2px;
            background: linear-gradient(to bottom, #007bff, #0056b3);
            opacity: 0.7;
            border-radius: 1px;
        }
        
        /* Líneas horizontales para cada nodo */
        .jstree-default .jstree-children .jstree-node {
            position: relative;
        }
        
        .jstree-default .jstree-children .jstree-node:before {
            content: '';
            position: absolute;
            left: -25px;
            top: 18px;
            width: 22px;
            height: 2px;
            background: linear-gradient(to right, #007bff, #0056b3);
            opacity: 0.7;
            border-radius: 1px;
        }
        
        /* Ocultar línea vertical en el último nodo */
        .jstree-default .jstree-children .jstree-node:last-child:after {
            content: '';
            position: absolute;
            left: -26px;
            top: 20px;
            bottom: -18px;
            width: 4px;
            background: white;
            z-index: 1;
        }
        
        /* Estilos específicos por nivel de profundidad mejorados */
        .jstree-root-level > .jstree-anchor {
            font-weight: bold;
            border-left: 4px solid #007bff;
            padding-left: 12px;
            background: linear-gradient(to right, rgba(0, 123, 255, 0.1), transparent);
            font-size: 15px;
        }
        
        .jstree-level-2 > .jstree-anchor {
            border-left: 3px solid #28a745;
            padding-left: 10px;
            background: linear-gradient(to right, rgba(40, 167, 69, 0.08), transparent);
            font-size: 14px;
        }
        
        .jstree-level-3 > .jstree-anchor {
            border-left: 2px solid #ffc107;
            padding-left: 8px;
            background: linear-gradient(to right, rgba(255, 193, 7, 0.08), transparent);
            font-style: italic;
            font-size: 13px;
        }
        
        /* Estilos adicionales para niveles más profundos */
        .jstree-level-4 > .jstree-anchor {
            border-left: 2px solid #dc3545;
            padding-left: 8px;
            background: linear-gradient(to right, rgba(220, 53, 69, 0.08), transparent);
            font-size: 13px;
            opacity: 0.9;
        }
        
        .jstree-level-5 > .jstree-anchor {
            border-left: 2px solid #6f42c1;
            padding-left: 8px;
            background: linear-gradient(to right, rgba(111, 66, 193, 0.08), transparent);
            font-size: 12px;
            opacity: 0.9;
        }
        
        /* Efectos visuales para nodos inactivos */
        .jstree-default .jstree-node[data-type="inactive"] > .jstree-anchor {
            opacity: 0.7;
            background-color: #f8f9fa;
        }
        
        /* Mejorar la visualización jerárquica */
        .jstree-default .jstree-node {
            margin: 2px 0;
            min-height: 36px;
            line-height: 36px;
        }
        
        .jstree-default .jstree-anchor {
            padding: 0 10px 0 8px;
            border-radius: 6px;
            line-height: 36px;
            height: 36px;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .jstree-default .jstree-icon {
            width: 18px;
            height: 18px;
            margin-top: 9px;
            margin-right: 8px;
        }
        
        /* Hover effects para nodos mejorados */
        .jstree-default .jstree-hovered {
            background: rgba(0, 123, 255, 0.15);
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0, 123, 255, 0.2);
        }
        
        .jstree-default .jstree-clicked {
            background: rgba(0, 123, 255, 0.25);
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);
        }
        
        .jstree-default .jstree-anchor:hover {
            background: rgba(0, 123, 255, 0.1);
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <h1 class="text-center mb-4">Práctica 10 - Árbol Recursivo de Ejecutivos</h1>
        
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4>
                            <i class="fas fa-sitemap"></i>
                            Gestión Jerárquica de Ejecutivos
                        </h4>
                    </div>
                    <div class="col-md-4 text-right">
                        <button class="btn btn-outline-secondary" onclick="window.location.href='index.php'">
                            <i class="fas fa-arrow-left"></i> Volver a Citas
                        </button>
                        <button class="btn btn-info" onclick="recargarArbol()">
                            <i class="fas fa-sync-alt"></i> Recargar
                        </button>
                        <button class="btn btn-success" onclick="mostrarModalCrear()">
                            <i class="fas fa-plus"></i> Nuevo
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                
                <!-- Panel de búsqueda y filtros -->
                <div class="search-section">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="buscarTexto"><strong>Buscar Ejecutivos:</strong></label>
                            <input type="text" id="buscarTexto" class="form-control" placeholder="Buscar por nombre...">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="mostrarOcultos" checked>
                                <label class="form-check-label" for="mostrarOcultos">
                                    Mostrar ejecutivos ocultos
                                </label>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-primary" onclick="aplicarFiltros()">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button class="btn btn-secondary" onclick="expandirTodo()">
                                <i class="fas fa-expand-arrows-alt"></i> Expandir
                            </button>
                            <button class="btn btn-secondary ml-2" onclick="colapsarTodo()">
                                <i class="fas fa-compress-arrows-alt"></i> Colapsar
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Panel de estadísticas (estilo similar a index.php) -->
                <div class="stats-container">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h5 class="text-primary mb-1">
                                    <i class="fas fa-users"></i>
                                    <span id="totalEjecutivos" class="badge badge-primary">-</span>
                                </h5>
                                <small class="text-muted">Total de Ejecutivos</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h5 class="text-success mb-1">
                                    <i class="fas fa-eye"></i>
                                    <span id="ejecutivosActivos" class="badge badge-success">-</span>
                                </h5>
                                <small class="text-muted">Ejecutivos Activos</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h5 class="text-secondary mb-1">
                                    <i class="fas fa-eye-slash"></i>
                                    <span id="ejecutivosOcultos" class="badge badge-secondary">-</span>
                                </h5>
                                <small class="text-muted">Ejecutivos Ocultos</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h5 class="text-info mb-1">
                                    <i class="fas fa-crown"></i>
                                    <span id="nodosRaiz" class="badge badge-info">-</span>
                                </h5>
                                <small class="text-muted">Nodos Raíz</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Contenedor principal con dos columnas -->
                <div class="row">
                    <!-- Columna izquierda: Árbol jerárquico -->
                    <div class="col-md-8">
                        <div class="tree-container">
                            <h4><i class="fas fa-tree"></i> Estructura Jerárquica</h4>
                            <div class="breadcrumb-custom">
                                <span id="rutaSeleccionada">Selecciona un nodo para ver su ruta</span>
                            </div>
                            <div id="jstree"></div>
                        </div>
                    </div>
                    
                    <!-- Columna derecha: Panel de acciones -->
                    <div class="col-md-4">
                        <div class="actions-panel">
                            <h4><i class="fas fa-tools"></i> Acciones</h4>
                            
                            <!-- Información del nodo seleccionado -->
                            <div id="nodoSeleccionadoInfo" style="display: none;" class="node-info">
                                <h6><i class="fas fa-user"></i> Nodo Seleccionado</h6>
                                <p><strong>ID:</strong> <span id="selectedId">-</span></p>
                                <p><strong>Nombre:</strong> <span id="selectedNombre">-</span></p>
                                <p><strong>Teléfono:</strong> <span id="selectedTelefono">-</span></p>
                                <p><strong>Estado:</strong> <span id="selectedEstado">-</span></p>
                                <p><strong>Padre:</strong> <span id="selectedPadre">-</span></p>
                            </div>
                            
                            <hr>
                            
                            <!-- Botones de acción -->
                            <div class="text-center">
                                <button class="btn btn-success btn-action" onclick="mostrarModalCrear()">
                                    <i class="fas fa-plus"></i> Crear Hijo
                                </button>
                                
                                <button class="btn btn-primary btn-action" onclick="mostrarModalEditar()" id="btnEditar" disabled>
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                
                                <button class="btn btn-warning btn-action" onclick="toggleEstado()" id="btnToggle" disabled>
                                    <i class="fas fa-eye-slash"></i> Ocultar
                                </button>
                                
                                <button class="btn btn-info btn-action" onclick="moverNodo()" id="btnMover" disabled>
                                    <i class="fas fa-arrows-alt"></i> Mover
                                </button>
                                
                                <button class="btn btn-secondary btn-action" onclick="expandirTodo()">
                                    <i class="fas fa-expand-arrows-alt"></i> Expandir Todo
                                </button>
                                
                                <button class="btn btn-secondary btn-action" onclick="colapsarTodo()">
                                    <i class="fas fa-compress-arrows-alt"></i> Colapsar Todo
                                </button>
                            </div>
                            
                            <hr>
                            
                            <!-- Filtros -->
                            <h6><i class="fas fa-filter"></i> Filtros</h6>
                            <div class="form-group">
                                <label>Buscar:</label>
                                <input type="text" class="form-control" id="buscarTextoAcciones" placeholder="Nombre del ejecutivo...">
                            </div>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="mostrarOcultosAcciones" checked>
                                <label class="form-check-label" for="mostrarOcultosAcciones">
                                    Mostrar ejecutivos ocultos
                                </label>
                            </div>
                            
                            <button class="btn btn-outline-primary btn-sm mt-2" onclick="aplicarFiltros()">
                                <i class="fas fa-search"></i> Aplicar Filtros
                            </button>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
    
    <!-- Modal para Crear/Editar -->
    <div class="modal fade" id="modalEjecutivo" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitulo">Crear Ejecutivo</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="formEjecutivo">
                        <input type="hidden" id="ejecutivoId">
                        <input type="hidden" id="ejecutivoPadreId">
                        
                        <div class="form-group">
                            <label for="ejecutivoNombre"><i class="fas fa-user"></i> Nombre *</label>
                            <input type="text" class="form-control" id="ejecutivoNombre" required placeholder="Nombre completo del ejecutivo">
                        </div>
                        
                        <div class="form-group">
                            <label for="ejecutivoTelefono"><i class="fas fa-phone"></i> Teléfono *</label>
                            <input type="text" class="form-control" id="ejecutivoTelefono" required placeholder="Ej: 555-1234">
                        </div>
                        
                        <div class="form-group">
                            <label for="ejecutivoPadre"><i class="fas fa-sitemap"></i> Ejecutivo Padre</label>
                            <select class="form-control" id="ejecutivoPadre">
                                <option value="">Sin padre (Nodo raíz)</option>
                            </select>
                            <small class="form-text text-muted">Selecciona el ejecutivo superior en la jerarquía</small>
                        </div>
                        
                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="ejecutivoActivo" checked>
                                <label class="form-check-label" for="ejecutivoActivo">
                                    <i class="fas fa-eye"></i> Ejecutivo visible
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarEjecutivo()">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal para Mover Nodo -->
    <div class="modal fade" id="modalMover" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Mover Ejecutivo</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Mover <strong id="nombreMover"></strong> a:</p>
                    <select class="form-control" id="nuevoPadre">
                        <option value="">Sin padre (Nodo raíz)</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="confirmarMover()">
                        <i class="fas fa-arrows-alt"></i> Mover
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Variables globales
        var ejecutivos = [];
        var nodosTree = [];
        var nodoSeleccionado = null;
        var modoEdicion = false;
        
        // Inicialización
        $(document).ready(function() {
            cargarEjecutivos();
            configurarEventos();
        });
        
        // =====================================
        // FUNCIONES DE CARGA DE DATOS
        // =====================================
        
        function cargarEjecutivos() {
            $.ajax({
                url: 'server/controlador_ejecutivos.php',
                type: 'POST',
                data: { action: 'obtener_ejecutivos_jerarquia' },
                dataType: 'json',
                success: function(response) {
                    console.log('Respuesta del servidor:', response);
                    if(response.success) {
                        ejecutivos = response.data;
                        console.log('Ejecutivos cargados:', ejecutivos.length);
                        console.log('Ejecutivos:', ejecutivos);
                        actualizarEstadisticas();
                        generarArbolJsTree();
                    } else {
                        alert('Error al cargar ejecutivos: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error de conexión al servidor');
                }
            });
        }
        
        // =====================================
        // FUNCIONES DE JSTREE
        // =====================================
        
        function generarArbolJsTree() {
            // Generar estructura de nodos para jsTree
            nodosTree = [];
            
            console.log('Generando árbol con', ejecutivos.length, 'ejecutivos');
            
            ejecutivos.forEach(function(ejecutivo) {
                var estado = ejecutivo.eli_eje == 1 ? 'visible' : 'oculto';
                var icono = ejecutivo.eli_eje == 1 ? 'fas fa-user text-success' : 'fas fa-user-slash text-muted';
                var tipo = 'default';
                
                // Determinar tipo de nodo para mejor visualización
                if (!ejecutivo.id_padre) {
                    tipo = 'root';
                    icono = 'fas fa-crown text-warning';
                } else if (ejecutivo.eli_eje == 1) {
                    tipo = 'active';
                } else {
                    tipo = 'inactive';
                }
                
                var nodo = {
                    id: ejecutivo.id_eje,
                    parent: ejecutivo.id_padre || '#',
                    text: ejecutivo.nom_eje + ' <span class="badge badge-' + (ejecutivo.eli_eje == 1 ? 'success' : 'secondary') + ' status-badge">' + estado + '</span>',
                    icon: icono,
                    type: tipo,
                    data: ejecutivo
                };
                
                nodosTree.push(nodo);
            });
            
            console.log('Nodos generados:', nodosTree.length);
            console.log('Nodos:', nodosTree);
            
            // Inicializar jsTree con drag & drop y mejor visualización jerárquica
            $('#jstree').jstree('destroy');
            
            if (nodosTree.length === 0) {
                $('#jstree').html('<p class="text-center text-muted">No hay ejecutivos para mostrar</p>');
                return;
            }
            
            $('#jstree').jstree({
                'core': {
                    'data': nodosTree,
                    'check_callback': function(operation, node, parent, position, more) {
                        // Permitir todas las operaciones de drag & drop
                        if(operation === 'move_node') {
                            // Verificar que no se mueva un nodo a sí mismo o a sus descendientes
                            return !esDescendiente(node.id, parent.id);
                        }
                        return true;
                    },
                    'themes': {
                        'responsive': true,
                        'variant': 'large',
                        'stripes': false,  // Desactivar líneas alternas para mejor legibilidad
                        'dots': true,      // Mostrar puntos de conexión
                        'icons': true
                    }
                },
                'dnd': {
                    'is_draggable': function(nodes) {
                        // Solo permitir arrastrar un nodo a la vez
                        return nodes.length === 1;
                    },
                    'copy': false
                },
                'plugins': ['search', 'state', 'wholerow', 'dnd', 'types'],
                'state': {
                    'key': 'jstree_ejecutivos_jerarquia'
                },
                'types': {
                    'default': {
                        'icon': 'fas fa-user'
                    },
                    'root': {
                        'icon': 'fas fa-crown text-warning'
                    },
                    'active': {
                        'icon': 'fas fa-user text-success'
                    },
                    'inactive': {
                        'icon': 'fas fa-user-slash text-muted'
                    }
                }
            }).on('ready.jstree', function() {
                // Expandir todo automáticamente para mostrar la jerarquía completa
                $('#jstree').jstree('open_all');
                
                // Aplicar clases específicas para mejorar la visualización
                setTimeout(function() {
                    $('#jstree').find('.jstree-node[aria-level="1"]').addClass('jstree-root-level');
                    $('#jstree').find('.jstree-node[aria-level="2"]').addClass('jstree-level-2');
                    $('#jstree').find('.jstree-node[aria-level="3"]').addClass('jstree-level-3');
                    $('#jstree').find('.jstree-node[aria-level="4"]').addClass('jstree-level-4');
                    $('#jstree').find('.jstree-node[aria-level="5"]').addClass('jstree-level-5');
                    
                    // Añadir iconos específicos por nivel
                    $('#jstree').find('.jstree-root-level .jstree-icon').removeClass('fas fa-user').addClass('fas fa-crown');
                    $('#jstree').find('.jstree-level-2 .jstree-icon').removeClass('fas fa-user').addClass('fas fa-user-tie');
                    $('#jstree').find('.jstree-level-3 .jstree-icon').removeClass('fas fa-user').addClass('fas fa-user-friends');
                    $('#jstree').find('.jstree-level-4 .jstree-icon').removeClass('fas fa-user').addClass('fas fa-user-check');
                    $('#jstree').find('.jstree-level-5 .jstree-icon').removeClass('fas fa-user').addClass('fas fa-user-plus');
                    
                    console.log('✅ Clases de nivel aplicadas correctamente al árbol de ejecutivos');
                }, 150);
            });
        }
        
        function configurarEventos() {
            // Evento de selección de nodo
            $('#jstree').on('select_node.jstree', function (e, data) {
                nodoSeleccionado = data.node;
                mostrarInformacionNodo(data.node.data);
                mostrarRutaNodo(data.node);
                habilitarBotones();
            });
            
            // Evento de drag & drop completado
            $('#jstree').on('move_node.jstree', function (e, data) {
                var nodoMovido = data.node;
                var nuevoPadre = data.parent;
                var ejecutivoId = nodoMovido.id;
                var nuevoPadreId = nuevoPadre === '#' ? null : nuevoPadre;
                
                // Mostrar mensaje de estado
                mostrarMensajeDragDrop('Moviendo ejecutivo...', false);
                
                // Realizar actualización en el backend
                $.ajax({
                    url: 'server/controlador_ejecutivos.php',
                    type: 'POST',
                    data: {
                        action: 'mover_ejecutivo',
                        id_eje: ejecutivoId,
                        id_padre: nuevoPadreId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if(response.success) {
                            mostrarMensajeDragDrop('✓ Ejecutivo movido correctamente', true);
                            // Actualizar datos locales
                            var ejecutivo = ejecutivos.find(e => e.id_eje == ejecutivoId);
                            if(ejecutivo) {
                                ejecutivo.id_padre = nuevoPadreId;
                            }
                            actualizarEstadisticas();
                        } else {
                            mostrarMensajeDragDrop('✗ Error: ' + response.message, false, true);
                            // Revertir el cambio visual si falla
                            cargarEjecutivos();
                        }
                        setTimeout(function() {
                            ocultarMensajeDragDrop();
                        }, 3000);
                    },
                    error: function() {
                        mostrarMensajeDragDrop('✗ Error de conexión', false, true);
                        cargarEjecutivos(); // Revertir cambios
                        setTimeout(function() {
                            ocultarMensajeDragDrop();
                        }, 3000);
                    }
                });
            });
            
            // Búsqueda en tiempo real
            $('#buscarTexto, #buscarTextoAcciones').on('keyup', function() {
                var searchString = $(this).val();
                $('#jstree').jstree('search', searchString);
            });
            
            // Filtro de mostrar ocultos
            $('#mostrarOcultos, #mostrarOcultosAcciones').on('change', function() {
                aplicarFiltros();
            });
        }
        
        // =====================================
        // FUNCIONES DE INTERFAZ
        // =====================================
        
        function mostrarInformacionNodo(ejecutivo) {
            $('#selectedId').text(ejecutivo.id_eje);
            $('#selectedNombre').text(ejecutivo.nom_eje);
            $('#selectedTelefono').text(ejecutivo.tel_eje);
            $('#selectedEstado').html('<span class="badge badge-' + (ejecutivo.eli_eje == 1 ? 'success' : 'secondary') + '">' + (ejecutivo.eli_eje == 1 ? 'Visible' : 'Oculto') + '</span>');
            
            // Buscar nombre del padre
            var padre = ejecutivos.find(e => e.id_eje == ejecutivo.id_padre);
            $('#selectedPadre').text(padre ? padre.nom_eje : 'Sin padre (Raíz)');
            
            $('#nodoSeleccionadoInfo').show();
        }
        
        function mostrarRutaNodo(nodo) {
            var ruta = [];
            var nodoActual = nodo;
            
            while(nodoActual) {
                ruta.unshift(nodoActual.text.replace(/<[^>]*>/g, '')); // Quitar HTML
                nodoActual = $('#jstree').jstree('get_node', nodoActual.parent);
                if(nodoActual.id === '#') break;
            }
            
            $('#rutaSeleccionada').text(ruta.join(' > '));
        }
        
        function habilitarBotones() {
            $('#btnEditar, #btnToggle, #btnMover').prop('disabled', false);
            
            // Cambiar texto del botón toggle según estado
            var ejecutivo = nodoSeleccionado.data;
            if(ejecutivo.eli_eje == 1) {
                $('#btnToggle').html('<i class="fas fa-eye-slash"></i> Ocultar').removeClass('btn-success').addClass('btn-warning');
            } else {
                $('#btnToggle').html('<i class="fas fa-eye"></i> Mostrar').removeClass('btn-warning').addClass('btn-success');
            }
        }
        
        function actualizarEstadisticas() {
            var total = ejecutivos.length;
            var activos = ejecutivos.filter(e => e.eli_eje == 1).length;
            var ocultos = total - activos;
            var raiz = ejecutivos.filter(e => !e.id_padre).length;
            
            $('#totalEjecutivos').text(total);
            $('#ejecutivosActivos').text(activos);
            $('#ejecutivosOcultos').text(ocultos);
            $('#nodosRaiz').text(raiz);
        }
        
        // =====================================
        // FUNCIONES DE CRUD
        // =====================================
        
        function mostrarModalCrear() {
            modoEdicion = false;
            $('#modalTitulo').text('Crear Nuevo Ejecutivo');
            $('#ejecutivoId').val('');
            $('#ejecutivoNombre').val('');
            $('#ejecutivoTelefono').val('');
            $('#ejecutivoActivo').prop('checked', true);
            
            // Establecer padre seleccionado si hay uno
            if(nodoSeleccionado) {
                $('#ejecutivoPadreId').val(nodoSeleccionado.id);
                cargarSelectPadres(nodoSeleccionado.id);
            } else {
                $('#ejecutivoPadreId').val('');
                cargarSelectPadres();
            }
            
            $('#modalEjecutivo').modal('show');
        }
        
        function mostrarModalEditar() {
            if(!nodoSeleccionado) return;
            
            modoEdicion = true;
            var ejecutivo = nodoSeleccionado.data;
            
            $('#modalTitulo').text('Editar Ejecutivo');
            $('#ejecutivoId').val(ejecutivo.id_eje);
            $('#ejecutivoNombre').val(ejecutivo.nom_eje);
            $('#ejecutivoTelefono').val(ejecutivo.tel_eje);
            $('#ejecutivoActivo').prop('checked', ejecutivo.eli_eje == 1);
            
            cargarSelectPadres(null, ejecutivo.id_padre);
            
            $('#modalEjecutivo').modal('show');
        }
        
        function cargarSelectPadres(padreSeleccionado = null, valorActual = null) {
            var select = $('#ejecutivoPadre');
            select.empty();
            select.append('<option value="">Sin padre (Nodo raíz)</option>');
            
            ejecutivos.filter(e => e.eli_eje == 1).forEach(function(ejecutivo) {
                // No incluir el nodo actual (para evitar referencias circulares)
                if(modoEdicion && ejecutivo.id_eje == $('#ejecutivoId').val()) return;
                
                var selected = (valorActual && valorActual == ejecutivo.id_eje) || (padreSeleccionado == ejecutivo.id_eje) ? 'selected' : '';
                select.append('<option value="' + ejecutivo.id_eje + '" ' + selected + '>' + ejecutivo.nom_eje + '</option>');
            });
        }
        
        function guardarEjecutivo() {
            var datos = {
                action: modoEdicion ? 'actualizar_ejecutivo' : 'crear_ejecutivo',
                nom_eje: $('#ejecutivoNombre').val().trim(),
                tel_eje: $('#ejecutivoTelefono').val().trim(),
                id_padre: $('#ejecutivoPadre').val() || null,
                eli_eje: $('#ejecutivoActivo').is(':checked') ? 1 : 0
            };
            
            if(modoEdicion) {
                datos.id_eje = $('#ejecutivoId').val();
            }
            
            // Validaciones
            if(!datos.nom_eje) {
                alert('El nombre es requerido');
                return;
            }
            if(!datos.tel_eje) {
                alert('El teléfono es requerido');
                return;
            }
            
            $.ajax({
                url: 'server/controlador_ejecutivos.php',
                type: 'POST',
                data: datos,
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        $('#modalEjecutivo').modal('hide');
                        cargarEjecutivos();
                        alert('Ejecutivo ' + (modoEdicion ? 'actualizado' : 'creado') + ' correctamente');
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error de conexión al servidor');
                }
            });
        }
        
        function toggleEstado() {
            if(!nodoSeleccionado) return;
            
            var ejecutivo = nodoSeleccionado.data;
            var nuevoEstado = ejecutivo.eli_eje == 1 ? 0 : 1;
            var accion = nuevoEstado == 1 ? 'mostrar' : 'ocultar';
            
            if(confirm('¿Está seguro de ' + accion + ' a ' + ejecutivo.nom_eje + '?')) {
                $.ajax({
                    url: 'server/controlador_ejecutivos.php',
                    type: 'POST',
                    data: {
                        action: 'toggle_estado_ejecutivo',
                        id_eje: ejecutivo.id_eje,
                        eli_eje: nuevoEstado
                    },
                    dataType: 'json',
                    success: function(response) {
                        if(response.success) {
                            cargarEjecutivos();
                            alert('Estado actualizado correctamente');
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error de conexión al servidor');
                    }
                });
            }
        }
        
        function moverNodo() {
            if(!nodoSeleccionado) return;
            
            var ejecutivo = nodoSeleccionado.data;
            $('#nombreMover').text(ejecutivo.nom_eje);
            
            // Cargar select de nuevos padres
            var select = $('#nuevoPadre');
            select.empty();
            select.append('<option value="">Sin padre (Nodo raíz)</option>');
            
            ejecutivos.filter(e => e.eli_eje == 1 && e.id_eje != ejecutivo.id_eje).forEach(function(eje) {
                // Evitar crear referencias circulares (no puede ser hijo de sí mismo o de sus descendientes)
                var selected = eje.id_eje == ejecutivo.id_padre ? 'selected' : '';
                select.append('<option value="' + eje.id_eje + '" ' + selected + '>' + eje.nom_eje + '</option>');
            });
            
            $('#modalMover').modal('show');
        }
        
        function confirmarMover() {
            var ejecutivo = nodoSeleccionado.data;
            var nuevoPadreId = $('#nuevoPadre').val() || null;
            
            $.ajax({
                url: 'server/controlador_ejecutivos.php',
                type: 'POST',
                data: {
                    action: 'mover_ejecutivo',
                    id_eje: ejecutivo.id_eje,
                    id_padre: nuevoPadreId
                },
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        $('#modalMover').modal('hide');
                        cargarEjecutivos();
                        alert('Ejecutivo movido correctamente');
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error de conexión al servidor');
                }
            });
        }
        
        // =====================================
        // FUNCIONES AUXILIARES
        // =====================================
        
        function recargarArbol() {
            cargarEjecutivos();
        }
        
        function expandirTodo() {
            $('#jstree').jstree('open_all');
        }
        
        function colapsarTodo() {
            $('#jstree').jstree('close_all');
        }
        
        function aplicarFiltros() {
            // Esta función se puede expandir para más filtros
            cargarEjecutivos();
        }
        
        // Función para verificar si un nodo es descendiente de otro
        function esDescendiente(nodoId, posibleAncestroId) {
            if (nodoId === posibleAncestroId) {
                return true; // Un nodo es descendiente de sí mismo
            }
            
            var ejecutivo = ejecutivos.find(e => e.id_eje == nodoId);
            while (ejecutivo && ejecutivo.id_padre) {
                if (ejecutivo.id_padre == posibleAncestroId) {
                    return true;
                }
                ejecutivo = ejecutivos.find(e => e.id_eje == ejecutivo.id_padre);
            }
            return false;
        }
        
        // Funciones para mostrar mensajes de drag & drop
        function mostrarMensajeDragDrop(mensaje, exito, error) {
            var $status = $('.drag-status');
            if ($status.length === 0) {
                $status = $('<div class="drag-status"></div>');
                $('body').append($status);
            }
            
            $status.removeClass('success error').text(mensaje);
            
            if (exito) {
                $status.addClass('success');
            } else if (error) {
                $status.addClass('error');
            }
            
            $status.fadeIn();
        }
        
        function ocultarMensajeDragDrop() {
            $('.drag-status').fadeOut();
        }
        
    </script>
</body>
</html>
