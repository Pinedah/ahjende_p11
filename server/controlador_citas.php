<?php
// Habilitar reporte de errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log de debugging
file_put_contents('debug.log', '[' . date('Y-m-d H:i:s') . '] REQUEST recibido: ' . print_r($_POST, true) . "\n", FILE_APPEND);

include '../inc/conexion.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	
	// Validar que action esté presente
	if (!isset($_POST['action'])) {
		echo respuestaError('Acción no especificada');
		exit;
	}
	
	$action = escape($_POST['action'], $connection);
	
	switch($action) {

		case 'test_conexion':
			echo respuestaExito(['timestamp' => date('Y-m-d H:i:s')], 'Controlador funcionando correctamente');
		break;

		case 'obtener_citas':
			$fecha_filtro = isset($_POST['fecha_filtro']) ? escape($_POST['fecha_filtro'], $connection) : null;
			$id_ejecutivo = isset($_POST['id_ejecutivo']) ? intval($_POST['id_ejecutivo']) : null;
			$incluir_planteles_asociados = isset($_POST['incluir_planteles_asociados']) ? boolval($_POST['incluir_planteles_asociados']) : false;
			
			// Obtener todas las columnas de la tabla cita
			$queryColumnas = "SHOW COLUMNS FROM cita";
			$columnas = ejecutarConsulta($queryColumnas, $connection);
			
			$camposSelect = [];
			if ($columnas) {
				foreach ($columnas as $columna) {
					$camposSelect[] = 'c.' . $columna['Field'];
				}
			}
			
			// Agregar el campo del ejecutivo
			$camposSelect[] = 'e.nom_eje';
			$selectFields = implode(', ', $camposSelect);
			
			// Construir la consulta base
			$whereConditions = ['c.eli_cit = 1'];
			
			if ($fecha_filtro) {
				$whereConditions[] = "c.cit_cit = '$fecha_filtro'";
			}
			
			// Si se especifica un ejecutivo y se incluyen planteles asociados
			if ($id_ejecutivo && $incluir_planteles_asociados) {
				// Obtener todos los ejecutivos accesibles para este ejecutivo
				$ejecutivosAccesibles = obtenerEjecutivosAccesibles($id_ejecutivo, $connection);
				
				if (!empty($ejecutivosAccesibles)) {
					$ejecutivosIds = implode(',', $ejecutivosAccesibles);
					$whereConditions[] = "c.id_eje2 IN ($ejecutivosIds)";
				} else {
					// Si no hay ejecutivos accesibles, solo mostrar propias
					$whereConditions[] = "c.id_eje2 = $id_ejecutivo";
				}
			} elseif ($id_ejecutivo) {
				// Solo filtrar por el ejecutivo especificado
				$whereConditions[] = "c.id_eje2 = $id_ejecutivo";
			}
			
			$whereClause = implode(' AND ', $whereConditions);
			
			if ($fecha_filtro) {
				// Filtro por fecha específica
				$query = "SELECT $selectFields 
						 FROM cita c
						 LEFT JOIN ejecutivo e ON c.id_eje2 = e.id_eje
						 WHERE $whereClause
						 ORDER BY c.hor_cit ASC";
			} else {
				// Todas las citas para búsqueda
				$query = "SELECT $selectFields 
						 FROM cita c
						 LEFT JOIN ejecutivo e ON c.id_eje2 = e.id_eje
						 WHERE $whereClause
						 ORDER BY c.cit_cit DESC, c.hor_cit ASC";
			}

			$datos = ejecutarConsulta($query, $connection);

			if($datos !== false) {
				echo respuestaExito($datos, 'Citas obtenidas correctamente');
			} else {
				echo respuestaError('Error al consultar citas: ' . mysqli_error($connection) . ' Query: ' . $query);
			}
		break;

		case 'obtener_ejecutivos':
			$query = "SELECT id_eje, nom_eje FROM ejecutivo ORDER BY nom_eje ASC";
			$datos = ejecutarConsulta($query, $connection);

			if($datos !== false) {
				echo respuestaExito($datos, 'Ejecutivos obtenidos correctamente');
			} else {
				echo respuestaError('Error al consultar ejecutivos: ' . mysqli_error($connection) . ' Query: ' . $query);
			}
		break;

		case 'guardar_cita':
			// Obtener estructura de la tabla para inserción dinámica
			$estructuraQuery = "DESCRIBE cita";
			$columnas = ejecutarConsulta($estructuraQuery, $connection);
			
			if($columnas === false) {
				echo respuestaError('Error al obtener estructura de tabla');
				break;
			}
			
			$camposInsertar = [];
			$valoresInsertar = [];
			
			foreach($columnas as $columna) {
				$nombreCol = $columna['Field'];
				
				// Saltar campo auto-increment
				if($nombreCol === 'id_cit') continue;
				
				// Establecer eli_cit = 1 por defecto para nuevas citas
				if($nombreCol === 'eli_cit') {
					$camposInsertar[] = $nombreCol;
					$valoresInsertar[] = "1";
					continue;
				}
				
				if(isset($_POST[$nombreCol]) && $_POST[$nombreCol] !== '') {
					$valor = escape($_POST[$nombreCol], $connection);
					$camposInsertar[] = $nombreCol;
					$valoresInsertar[] = "'$valor'";
				} else {
					// Solo permitir NULL si la columna lo acepta
					if($columna['Null'] === 'YES') {
						$camposInsertar[] = $nombreCol;
						$valoresInsertar[] = "NULL";
					} else {
						// Para campos que no permiten NULL, usar valores por defecto
						$camposInsertar[] = $nombreCol;
						if(strpos($columna['Type'], 'int') !== false) {
							$valoresInsertar[] = "0";
						} else if(strpos($columna['Type'], 'date') !== false) {
							$valoresInsertar[] = "CURDATE()";
						} else if(strpos($columna['Type'], 'time') !== false) {
							$valoresInsertar[] = "CURTIME()";
						} else {
							$valoresInsertar[] = "''";
						}
					}
				}
			}
			
			if(empty($camposInsertar)) {
				echo respuestaError('No hay campos válidos para insertar');
				break;
			}
			
			$query = "INSERT INTO cita (" . implode(', ', $camposInsertar) . ") VALUES (" . implode(', ', $valoresInsertar) . ")";
			
			if(mysqli_query($connection, $query)) {
				$nuevo_id = mysqli_insert_id($connection);
				
				// Registrar en historial
				$nombreCita = isset($_POST['nom_cit']) ? $_POST['nom_cit'] : 'Sin nombre';
				$descripcion = "Se creó nueva cita: '$nombreCita'";
				registrarHistorial($connection, $nuevo_id, 'alta', $descripcion);
				
				echo respuestaExito(['id' => $nuevo_id], 'Cita guardada correctamente');
			} else {
				echo respuestaError('Error al guardar cita: ' . mysqli_error($connection) . ' Query: ' . $query);
			}
		break;

		case 'actualizar_cita':
			$campo = escape($_POST['campo'], $connection);
			$valor = $_POST['valor']; // No escapar aún
			$id_cit = escape($_POST['id_cit'], $connection);
			
			// Obtener valor anterior para el historial
			$queryAnterior = "SELECT $campo, nom_cit FROM cita WHERE id_cit = '$id_cit'";
			$anteriorResult = ejecutarConsulta($queryAnterior, $connection);
			$valorAnterior = $anteriorResult ? $anteriorResult[0][$campo] : '';
			$nombreCita = $anteriorResult ? ($anteriorResult[0]['nom_cit'] ?: 'Sin nombre') : 'Sin nombre';
			
			// Verificar que la columna existe en la tabla (permite columnas dinámicas)
			$queryCheck = "SHOW COLUMNS FROM cita WHERE Field = '$campo'";
			$existe = ejecutarConsulta($queryCheck, $connection);
			
			if (!$existe || empty($existe)) {
				echo respuestaError('Campo no válido para actualización');
				break;
			}
			
			$columnaInfo = $existe[0];
			
			// Manejar valores vacíos según las restricciones de la columna
			if ($valor === '' || $valor === null) {
				if ($columnaInfo['Null'] === 'YES') {
					$valorSQL = 'NULL';
				} else {
					// Para campos que no permiten NULL, usar valores por defecto
					if (strpos($columnaInfo['Type'], 'int') !== false) {
						$valorSQL = "0";
					} else {
						$valorSQL = "''";
					}
				}
			} else {
				$valorEscapado = escape($valor, $connection);
				$valorSQL = "'$valorEscapado'";
			}
			
			$query = "UPDATE cita SET $campo = $valorSQL WHERE id_cit = '$id_cit'";
			
			if(mysqli_query($connection, $query)) {
				// Registrar en historial solo si el valor cambió
				if($valorAnterior != $valor) {
					$campoLegible = strtoupper(str_replace('_', ' ', $campo));
					$valorAnteriorDisplay = $valorAnterior ?: '(vacío)';
					$valorNuevoDisplay = $valor ?: '(vacío)';
					$descripcion = "Se modificó $campoLegible de '$valorAnteriorDisplay' a '$valorNuevoDisplay' en la cita '$nombreCita'";
					registrarHistorial($connection, $id_cit, 'cambio', $descripcion);
				}
				
				echo respuestaExito(null, 'Cita actualizada correctamente');
			} else {
				echo respuestaError('Error al actualizar cita: ' . mysqli_error($connection) . ' Query: ' . $query);
			}
		break;

		case 'eliminar_cita':
			$id_cit = escape($_POST['id_cit'], $connection);
			
			if (!$id_cit) {
				echo respuestaError('ID de cita no proporcionado');
				break;
			}
			
			// Obtener información de la cita antes de eliminarla
			$queryInfo = "SELECT nom_cit FROM cita WHERE id_cit = '$id_cit' AND eli_cit = 1";
			$infoResult = ejecutarConsulta($queryInfo, $connection);
			$nombreCita = $infoResult ? ($infoResult[0]['nom_cit'] ?: 'Sin nombre') : 'Sin nombre';
			
			// Implementar eliminación suave (soft delete)
			$query = "UPDATE cita SET eli_cit = 0 WHERE id_cit = '$id_cit' AND eli_cit = 1";
			
			if(mysqli_query($connection, $query)) {
				if(mysqli_affected_rows($connection) > 0) {
					// Registrar en historial
					$descripcion = "Se eliminó (ocultó) la cita '$nombreCita'";
					registrarHistorial($connection, $id_cit, 'baja', $descripcion);
					
					echo respuestaExito(['id_eliminado' => $id_cit], 'Cita ocultada correctamente');
				} else {
					echo respuestaError('No se encontró la cita a eliminar o ya está eliminada');
				}
			} else {
				echo respuestaError('Error al eliminar cita: ' . mysqli_error($connection) . ' Query: ' . $query);
			}
		break;

		case 'obtener_estructura_tabla':
			$nombreTabla = 'cita';
			$query = "SHOW COLUMNS FROM $nombreTabla";
			$columnas = ejecutarConsulta($query, $connection);
			
			if($columnas !== false) {
				// Procesar columnas para el frontend
				$columnasConfig = [];
				
				foreach($columnas as $columna) {
					$campo = $columna['Field'];
					$tipo = $columna['Type'];
					
					// Ocultar el campo eli_cit de la interfaz (campo interno)
					if($campo === 'eli_cit') {
						continue;
					}
					
					// Configuración por defecto
					$config = [
						'key' => $campo,
						'header' => strtoupper(str_replace('_', ' ', $campo)),
						'type' => 'text',
						'width' => 120
					];
					
					// Configuraciones específicas por campo
					switch($campo) {
						case 'id_cit':
							$config['header'] = 'ID';
							$config['readOnly'] = true;
							$config['width'] = 60;
							break;
						case 'cit_cit':
							$config['header'] = 'FECHA';
							$config['type'] = 'date';
							$config['dateFormat'] = 'YYYY-MM-DD';
							$config['width'] = 120;
							break;
						case 'hor_cit':
							$config['header'] = 'HORA';
							$config['type'] = 'time';
							$config['timeFormat'] = 'HH:mm';
							$config['width'] = 100;
							break;
						case 'nom_cit':
							$config['header'] = 'NOMBRE';
							$config['width'] = 200;
							break;
						case 'tel_cit':
							$config['header'] = 'TELÉFONO';
							$config['width'] = 150;
							break;
						case 'id_eje2':
							$config['header'] = 'EJECUTIVO';
							$config['type'] = 'dropdown';
							$config['width'] = 180;
							break;
						default:
							// Detectar tipo automáticamente
							if(strpos($tipo, 'date') !== false) {
								$config['type'] = 'date';
								$config['dateFormat'] = 'YYYY-MM-DD';
							} elseif(strpos($tipo, 'time') !== false) {
								$config['type'] = 'time';
								$config['timeFormat'] = 'HH:mm';
							} elseif(strpos($tipo, 'int') !== false) {
								$config['type'] = 'numeric';
							} elseif(strpos($tipo, 'decimal') !== false || strpos($tipo, 'float') !== false) {
								$config['type'] = 'numeric';
							}
							break;
					}
					
					$columnasConfig[] = $config;
				}
				
				// Agregar columna de horario al inicio
				array_unshift($columnasConfig, [
					'key' => 'horario',
					'header' => 'HORARIO',
					'type' => 'text',
					'readOnly' => true,
					'className' => 'horario-column',
					'width' => 150
				]);
				
				echo respuestaExito($columnasConfig, 'Estructura de tabla obtenida correctamente');
			} else {
				echo respuestaError('Error al obtener estructura de tabla: ' . mysqli_error($connection));
			}
		break;

		case 'crear_nueva_columna':
			$nombreTabla = 'cita';
			$nombreNuevaColumna = isset($_POST['nombre_columna']) ? escape($_POST['nombre_columna'], $connection) : '';
			$tipoColumna = isset($_POST['tipo_columna']) ? escape($_POST['tipo_columna'], $connection) : 'VARCHAR(100)';
			
			if(empty($nombreNuevaColumna)) {
				echo respuestaError("Nombre de columna no proporcionado");
				break;
			}
			
			// Validar que el nombre no contenga caracteres especiales
			if(!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $nombreNuevaColumna)) {
				echo respuestaError("Nombre de columna no válido. Use solo letras, números y guiones bajos.");
				break;
			}
			
			$query = "ALTER TABLE $nombreTabla ADD COLUMN $nombreNuevaColumna $tipoColumna";

			if(mysqli_query($connection, $query)){
				echo respuestaExito([
					'nombre_columna' => $nombreNuevaColumna,
					'tipo_columna' => $tipoColumna
				], "Columna '$nombreNuevaColumna' agregada correctamente");
			}else{
				echo respuestaError("Error al crear la nueva columna: " . mysqli_error($connection));
			}
		break;

		case 'obtener_historial_cita':
			$id_cit = escape($_POST['id_cit'], $connection);
			
			if (!$id_cit) {
				echo respuestaError('ID de cita no proporcionado');
				break;
			}
			
			$historial = obtenerHistorialCita($connection, $id_cit);
			
			if($historial !== false) {
				echo respuestaExito($historial, 'Historial obtenido correctamente');
			} else {
				echo respuestaError('Error al obtener historial: ' . mysqli_error($connection));
			}
		break;

		default:
			echo respuestaError('Acción no válida');
		break;
	}

	mysqli_close($connection);
	exit;
}

// =====================================
// FUNCIONES DE HISTORIAL DE CITAS
// =====================================

function registrarHistorial($connection, $id_cit, $tipo_movimiento, $descripcion, $responsable = null) {
	// Si no se proporciona responsable, seleccionar uno aleatorio
	if (!$responsable) {
		$queryEjecutivo = "SELECT nom_eje FROM ejecutivo ORDER BY RAND() LIMIT 1";
		$ejecutivoResult = ejecutarConsulta($queryEjecutivo, $connection);
		$responsable = $ejecutivoResult ? $ejecutivoResult[0]['nom_eje'] : 'Sistema';
	}
	
	$id_cit_escaped = escape($id_cit, $connection);
	$tipo_escaped = escape($tipo_movimiento, $connection);
	$desc_escaped = escape($descripcion, $connection);
	$resp_escaped = escape($responsable, $connection);
	
	$query = "INSERT INTO historial_cita (fec_his_cit, res_his_cit, mov_his_cit, des_his_cit, id_cit11) 
			  VALUES (NOW(), '$resp_escaped', '$tipo_escaped', '$desc_escaped', '$id_cit_escaped')";
	
	return mysqli_query($connection, $query);
}

function obtenerHistorialCita($connection, $id_cit) {
	$id_cit_escaped = escape($id_cit, $connection);
	
	$query = "SELECT * FROM historial_cita 
			  WHERE id_cit11 = '$id_cit_escaped' 
			  ORDER BY fec_his_cit DESC";
	
	return ejecutarConsulta($query, $connection);
}

// =====================================
// FUNCIONES DE PLANTELES ASOCIADOS
// =====================================

function obtenerEjecutivosAccesibles($id_ejecutivo, $connection) {
	$ejecutivosAccesibles = [];
	
	// 1. Incluir al ejecutivo principal
	$ejecutivosAccesibles[] = $id_ejecutivo;
	
	// 2. Obtener ejecutivos de su árbol (hijos recursivos)
	$hijosArbol = obtenerHijosRecursivos($id_ejecutivo, $connection);
	$ejecutivosAccesibles = array_merge($ejecutivosAccesibles, $hijosArbol);
	
	// 3. Obtener ejecutivos de planteles asociados
	$ejecutivosPlanteles = obtenerEjecutivosPlanteles($id_ejecutivo, $connection);
	$ejecutivosAccesibles = array_merge($ejecutivosAccesibles, $ejecutivosPlanteles);
	
	// Eliminar duplicados
	$ejecutivosAccesibles = array_unique($ejecutivosAccesibles);
	
	return $ejecutivosAccesibles;
}

function obtenerHijosRecursivos($id_ejecutivo, $connection, $visitados = []) {
	$hijos = [];
	
	// Evitar recursión infinita
	if (in_array($id_ejecutivo, $visitados)) {
		return $hijos;
	}
	
	$visitados[] = $id_ejecutivo;
	
	// Obtener hijos directos
	$query = "SELECT id_eje FROM ejecutivo WHERE id_padre = $id_ejecutivo AND eli_eje = 1";
	$hijosDirectos = ejecutarConsulta($query, $connection);
	
	if ($hijosDirectos) {
		foreach ($hijosDirectos as $hijo) {
			$hijos[] = $hijo['id_eje'];
			// Recursivamente obtener hijos de este hijo
			$nietosHijos = obtenerHijosRecursivos($hijo['id_eje'], $connection, $visitados);
			$hijos = array_merge($hijos, $nietosHijos);
		}
	}
	
	return $hijos;
}

function obtenerEjecutivosPlanteles($id_ejecutivo, $connection) {
	$ejecutivosPlanteles = [];
	
	// Obtener planteles asociados al ejecutivo
	$queryPlanteles = "SELECT DISTINCT pe.id_pla 
					   FROM planteles_ejecutivo pe 
					   WHERE pe.id_eje = $id_ejecutivo";
	
	$plantelesAsociados = ejecutarConsulta($queryPlanteles, $connection);
	
	if ($plantelesAsociados) {
		foreach ($plantelesAsociados as $plantel) {
			$id_pla = $plantel['id_pla'];
			
			// Obtener todos los ejecutivos de este plantel
			$queryEjecutivos = "SELECT id_eje FROM ejecutivo WHERE id_pla = $id_pla AND eli_eje = 1";
			$ejecutivosPlantel = ejecutarConsulta($queryEjecutivos, $connection);
			
			if ($ejecutivosPlantel) {
				foreach ($ejecutivosPlantel as $ejecutivo) {
					$ejecutivosPlanteles[] = $ejecutivo['id_eje'];
					
					// También incluir el árbol de cada ejecutivo del plantel
					$hijosEjecutivo = obtenerHijosRecursivos($ejecutivo['id_eje'], $connection);
					$ejecutivosPlanteles = array_merge($ejecutivosPlanteles, $hijosEjecutivo);
				}
			}
		}
	}
	
	return $ejecutivosPlanteles;
}
?>
