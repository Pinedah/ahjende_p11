<?php
// Habilitar reporte de errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log de debugging
file_put_contents('debug_ejecutivos.log', '[' . date('Y-m-d H:i:s') . '] REQUEST recibido: ' . print_r($_POST, true) . "\n", FILE_APPEND);

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
			echo respuestaExito(['timestamp' => date('Y-m-d H:i:s')], 'Controlador de ejecutivos funcionando correctamente');
		break;

		case 'obtener_ejecutivos_jerarquia':
			// Obtener todos los ejecutivos con sus relaciones jerárquicas
			$query = "SELECT e.id_eje, e.nom_eje, e.tel_eje, e.eli_eje, e.id_padre, e.id_pla, p.nom_pla 
					  FROM ejecutivo e 
					  LEFT JOIN plantel p ON e.id_pla = p.id_pla 
					  ORDER BY e.eli_eje DESC, e.nom_eje ASC";
			
			// Log para debugging
			file_put_contents('debug_ejecutivos.log', '[' . date('Y-m-d H:i:s') . '] Query ejecutivos jerarquía: ' . $query . "\n", FILE_APPEND);
			
			$datos = ejecutarConsulta($query, $connection);

			if($datos !== false) {
				file_put_contents('debug_ejecutivos.log', '[' . date('Y-m-d H:i:s') . '] Ejecutivos jerarquía encontrados: ' . count($datos) . "\n", FILE_APPEND);
				echo respuestaExito($datos, 'Ejecutivos obtenidos correctamente');
			} else {
				$error = mysqli_error($connection);
				file_put_contents('debug_ejecutivos.log', '[' . date('Y-m-d H:i:s') . '] Error MySQL ejecutivos jerarquía: ' . $error . "\n", FILE_APPEND);
				echo respuestaError('Error al consultar ejecutivos: ' . $error . ' Query: ' . $query);
			}
		break;

		case 'obtener_ejecutivos_con_planteles':
			// Obtener todos los ejecutivos con sus planteles asociados (permisos)
			$query = "SELECT e.id_eje, e.nom_eje, e.tel_eje, e.eli_eje, e.id_padre, e.id_pla, 
					         p.nom_pla as plantel_principal,
					         GROUP_CONCAT(DISTINCT CONCAT(pa.id_pla, ':', pa.nom_pla) SEPARATOR '|') as planteles_asociados,
					         COUNT(DISTINCT pe.id_pla) as total_planteles_asociados
					  FROM ejecutivo e 
					  LEFT JOIN plantel p ON e.id_pla = p.id_pla 
					  LEFT JOIN planteles_ejecutivo pe ON e.id_eje = pe.id_eje
					  LEFT JOIN plantel pa ON pe.id_pla = pa.id_pla
					  GROUP BY e.id_eje
					  ORDER BY e.eli_eje DESC, e.nom_eje ASC";
			
			// Log para debugging
			file_put_contents('debug_ejecutivos.log', '[' . date('Y-m-d H:i:s') . '] Query ejecutivos con planteles: ' . $query . "\n", FILE_APPEND);
			
			$datos = ejecutarConsulta($query, $connection);

			if($datos !== false) {
				// Procesar los datos para estructurar mejor los planteles asociados
				foreach($datos as &$ejecutivo) {
					$ejecutivo['planteles_asociados_array'] = [];
					if($ejecutivo['planteles_asociados']) {
						$planteles = explode('|', $ejecutivo['planteles_asociados']);
						foreach($planteles as $plantel) {
							$partes = explode(':', $plantel);
							if(count($partes) == 2) {
								$ejecutivo['planteles_asociados_array'][] = [
									'id_pla' => $partes[0],
									'nom_pla' => $partes[1]
								];
							}
						}
					}
				}
				
				file_put_contents('debug_ejecutivos.log', '[' . date('Y-m-d H:i:s') . '] Ejecutivos con planteles encontrados: ' . count($datos) . "\n", FILE_APPEND);
				echo respuestaExito($datos, 'Ejecutivos con planteles obtenidos correctamente');
			} else {
				$error = mysqli_error($connection);
				file_put_contents('debug_ejecutivos.log', '[' . date('Y-m-d H:i:s') . '] Error MySQL ejecutivos con planteles: ' . $error . "\n", FILE_APPEND);
				echo respuestaError('Error al consultar ejecutivos con planteles: ' . $error . ' Query: ' . $query);
			}
		break;

		case 'crear_ejecutivo':
			$nom_eje = escape($_POST['nom_eje'], $connection);
			$tel_eje = escape($_POST['tel_eje'], $connection);
			$id_padre = isset($_POST['id_padre']) && $_POST['id_padre'] !== '' ? escape($_POST['id_padre'], $connection) : null;
			$id_pla = isset($_POST['id_pla']) && $_POST['id_pla'] !== '' ? escape($_POST['id_pla'], $connection) : null;
			$eli_eje = isset($_POST['eli_eje']) ? intval($_POST['eli_eje']) : 1;
			
			// Validaciones
			if(empty($nom_eje)) {
				echo respuestaError('El nombre del ejecutivo es requerido');
				break;
			}
			
			if(empty($tel_eje)) {
				echo respuestaError('El teléfono del ejecutivo es requerido');
				break;
			}
			
			// Verificar que el padre existe si se especifica
			if($id_padre) {
				$queryPadre = "SELECT id_eje FROM ejecutivo WHERE id_eje = '$id_padre' AND eli_eje = 1";
				$padreExiste = ejecutarConsulta($queryPadre, $connection);
				
				if(!$padreExiste || empty($padreExiste)) {
					echo respuestaError('El ejecutivo padre especificado no existe o está inactivo');
					break;
				}
			}
			
			// Insertar nuevo ejecutivo
			$query = "INSERT INTO ejecutivo (nom_eje, tel_eje, eli_eje, id_padre, id_pla) 
					  VALUES ('$nom_eje', '$tel_eje', $eli_eje, " . ($id_padre ? "'$id_padre'" : "NULL") . ", " . ($id_pla ? "'$id_pla'" : "NULL") . ")";
			
			if(mysqli_query($connection, $query)) {
				$nuevo_id = mysqli_insert_id($connection);
				
				// Registrar en historial de citas si existe
				if(function_exists('registrarHistorial')) {
					$descripcion = "Se creó nuevo ejecutivo: '$nom_eje'";
					registrarHistorial($connection, 0, 'alta', $descripcion, $nom_eje);
				}
				
				echo respuestaExito(['id' => $nuevo_id], 'Ejecutivo creado correctamente');
			} else {
				echo respuestaError('Error al crear ejecutivo: ' . mysqli_error($connection) . ' Query: ' . $query);
			}
		break;

		case 'actualizar_ejecutivo':
			$id_eje = escape($_POST['id_eje'], $connection);
			$nom_eje = escape($_POST['nom_eje'], $connection);
			$tel_eje = escape($_POST['tel_eje'], $connection);
			$id_padre = isset($_POST['id_padre']) && $_POST['id_padre'] !== '' ? escape($_POST['id_padre'], $connection) : null;
			$id_pla = isset($_POST['id_pla']) && $_POST['id_pla'] !== '' ? escape($_POST['id_pla'], $connection) : null;
			$eli_eje = isset($_POST['eli_eje']) ? intval($_POST['eli_eje']) : 1;
			
			// Validaciones
			if(empty($id_eje)) {
				echo respuestaError('ID del ejecutivo es requerido');
				break;
			}
			
			if(empty($nom_eje)) {
				echo respuestaError('El nombre del ejecutivo es requerido');
				break;
			}
			
			if(empty($tel_eje)) {
				echo respuestaError('El teléfono del ejecutivo es requerido');
				break;
			}
			
			// Verificar que el ejecutivo existe
			$queryExiste = "SELECT nom_eje FROM ejecutivo WHERE id_eje = '$id_eje'";
			$ejecutivoExiste = ejecutarConsulta($queryExiste, $connection);
			
			if(!$ejecutivoExiste || empty($ejecutivoExiste)) {
				echo respuestaError('El ejecutivo especificado no existe');
				break;
			}
			
			$nombreAnterior = $ejecutivoExiste[0]['nom_eje'];
			
			// Verificar que el padre existe si se especifica y no es el mismo ejecutivo
			if($id_padre && $id_padre != $id_eje) {
				$queryPadre = "SELECT id_eje FROM ejecutivo WHERE id_eje = '$id_padre' AND eli_eje = 1";
				$padreExiste = ejecutarConsulta($queryPadre, $connection);
				
				if(!$padreExiste || empty($padreExiste)) {
					echo respuestaError('El ejecutivo padre especificado no existe o está inactivo');
					break;
				}
				
				// Verificar que no se cree una referencia circular
				if(esReferenciaCircular($connection, $id_eje, $id_padre)) {
					echo respuestaError('No se puede establecer esta relación padre-hijo porque crearía una referencia circular');
					break;
				}
			} elseif($id_padre == $id_eje) {
				echo respuestaError('Un ejecutivo no puede ser padre de sí mismo');
				break;
			}
			
			// Actualizar ejecutivo
			$query = "UPDATE ejecutivo 
					  SET nom_eje = '$nom_eje', 
						  tel_eje = '$tel_eje', 
						  eli_eje = $eli_eje, 
						  id_padre = " . ($id_padre ? "'$id_padre'" : "NULL") . ",
						  id_pla = " . ($id_pla ? "'$id_pla'" : "NULL") . "
					  WHERE id_eje = '$id_eje'";
			
			if(mysqli_query($connection, $query)) {
				// Registrar cambios en historial si existe
				if(function_exists('registrarHistorial') && $nombreAnterior != $nom_eje) {
					$descripcion = "Se modificó ejecutivo de '$nombreAnterior' a '$nom_eje'";
					registrarHistorial($connection, 0, 'cambio', $descripcion, $nom_eje);
				}
				
				echo respuestaExito(null, 'Ejecutivo actualizado correctamente');
			} else {
				echo respuestaError('Error al actualizar ejecutivo: ' . mysqli_error($connection) . ' Query: ' . $query);
			}
		break;

		case 'toggle_estado_ejecutivo':
			$id_eje = escape($_POST['id_eje'], $connection);
			$eli_eje = isset($_POST['eli_eje']) ? intval($_POST['eli_eje']) : 1;
			
			if(empty($id_eje)) {
				echo respuestaError('ID del ejecutivo es requerido');
				break;
			}
			
			// Obtener información del ejecutivo
			$queryInfo = "SELECT nom_eje, eli_eje FROM ejecutivo WHERE id_eje = '$id_eje'";
			$infoResult = ejecutarConsulta($queryInfo, $connection);
			
			if(!$infoResult || empty($infoResult)) {
				echo respuestaError('Ejecutivo no encontrado');
				break;
			}
			
			$nombreEjecutivo = $infoResult[0]['nom_eje'];
			$estadoAnterior = $infoResult[0]['eli_eje'];
			
			// Actualizar estado
			$query = "UPDATE ejecutivo SET eli_eje = $eli_eje WHERE id_eje = '$id_eje'";
			
			if(mysqli_query($connection, $query)) {
				// Registrar en historial si existe
				if(function_exists('registrarHistorial')) {
					$accion = $eli_eje == 1 ? 'mostró' : 'ocultó';
					$descripcion = "Se $accion el ejecutivo '$nombreEjecutivo'";
					registrarHistorial($connection, 0, $eli_eje == 1 ? 'alta' : 'baja', $descripcion, $nombreEjecutivo);
				}
				
				$mensaje = $eli_eje == 1 ? 'Ejecutivo mostrado correctamente' : 'Ejecutivo ocultado correctamente';
				echo respuestaExito(['nuevo_estado' => $eli_eje], $mensaje);
			} else {
				echo respuestaError('Error al cambiar estado del ejecutivo: ' . mysqli_error($connection) . ' Query: ' . $query);
			}
		break;

		case 'mover_ejecutivo':
			$id_eje = escape($_POST['id_eje'], $connection);
			$id_padre = isset($_POST['id_padre']) && $_POST['id_padre'] !== '' && $_POST['id_padre'] !== 'null' ? escape($_POST['id_padre'], $connection) : null;
			$id_pla = isset($_POST['id_pla']) && $_POST['id_pla'] !== '' && $_POST['id_pla'] !== 'null' ? escape($_POST['id_pla'], $connection) : null;
			
			// Log mejorado para debugging
			file_put_contents('debug_ejecutivos.log', '[' . date('Y-m-d H:i:s') . '] ==============================================' . "\n", FILE_APPEND);
			file_put_contents('debug_ejecutivos.log', '[' . date('Y-m-d H:i:s') . '] MOVER EJECUTIVO - REQUEST RECIBIDO' . "\n", FILE_APPEND);
			file_put_contents('debug_ejecutivos.log', '[' . date('Y-m-d H:i:s') . '] POST data completo: ' . print_r($_POST, true) . "\n", FILE_APPEND);
			file_put_contents('debug_ejecutivos.log', '[' . date('Y-m-d H:i:s') . '] Valores procesados:' . "\n", FILE_APPEND);
			file_put_contents('debug_ejecutivos.log', '[' . date('Y-m-d H:i:s') . '] - ID Ejecutivo: ' . $id_eje . "\n", FILE_APPEND);
			file_put_contents('debug_ejecutivos.log', '[' . date('Y-m-d H:i:s') . '] - ID Padre: ' . ($id_padre ? $id_padre : 'NULL') . "\n", FILE_APPEND);
			file_put_contents('debug_ejecutivos.log', '[' . date('Y-m-d H:i:s') . '] - ID Plantel: ' . ($id_pla ? $id_pla : 'NULL') . "\n", FILE_APPEND);
			
			if(empty($id_eje)) {
				file_put_contents('debug_ejecutivos.log', '[' . date('Y-m-d H:i:s') . '] ERROR: ID del ejecutivo vacío' . "\n", FILE_APPEND);
				echo respuestaError('ID del ejecutivo es requerido');
				break;
			}
			
			// Verificar que el ejecutivo existe
			$queryExiste = "SELECT nom_eje, id_padre, id_pla FROM ejecutivo WHERE id_eje = '$id_eje'";
			$ejecutivoExiste = ejecutarConsulta($queryExiste, $connection);
			
			if(!$ejecutivoExiste || empty($ejecutivoExiste)) {
				echo respuestaError('El ejecutivo especificado no existe');
				break;
			}
			
			$nombreEjecutivo = $ejecutivoExiste[0]['nom_eje'];
			$padreAnterior = $ejecutivoExiste[0]['id_padre'];
			$plantelAnterior = $ejecutivoExiste[0]['id_pla'];
			
			// Log de valores anteriores
			file_put_contents('debug_ejecutivos.log', '[' . date('Y-m-d H:i:s') . '] ANTES - Ejecutivo: ' . $nombreEjecutivo . ', Padre anterior: ' . $padreAnterior . ', Plantel anterior: ' . $plantelAnterior . "\n", FILE_APPEND);
			
			// Verificar que no se mueva a sí mismo
			if($id_padre == $id_eje) {
				echo respuestaError('Un ejecutivo no puede ser padre de sí mismo');
				break;
			}
			
			// Verificar que el nuevo padre existe si se especifica
			if($id_padre) {
				$queryPadre = "SELECT nom_eje FROM ejecutivo WHERE id_eje = '$id_padre' AND eli_eje = 1";
				$padreExiste = ejecutarConsulta($queryPadre, $connection);
				
				if(!$padreExiste || empty($padreExiste)) {
					echo respuestaError('El ejecutivo padre especificado no existe o está inactivo');
					break;
				}
				
				$nombrePadre = $padreExiste[0]['nom_eje'];
				
				// Verificar referencias circulares
				if(esReferenciaCircular($connection, $id_eje, $id_padre)) {
					echo respuestaError('No se puede mover a esta posición porque crearía una referencia circular');
					break;
				}
				
				file_put_contents('debug_ejecutivos.log', '[' . date('Y-m-d H:i:s') . '] NUEVO PADRE: ' . $nombrePadre . ' (ID: ' . $id_padre . ')' . "\n", FILE_APPEND);
			}
			
			// Verificar que el plantel existe si se especifica
			if($id_pla) {
				$queryPlantel = "SELECT nom_pla FROM plantel WHERE id_pla = '$id_pla'";
				$plantelExiste = ejecutarConsulta($queryPlantel, $connection);
				
				if(!$plantelExiste || empty($plantelExiste)) {
					echo respuestaError('El plantel especificado no existe');
					break;
				}
				
				$nombrePlantel = $plantelExiste[0]['nom_pla'];
				file_put_contents('debug_ejecutivos.log', '[' . date('Y-m-d H:i:s') . '] NUEVO PLANTEL: ' . $nombrePlantel . ' (ID: ' . $id_pla . ')' . "\n", FILE_APPEND);
			}
			
			// Mover ejecutivo
			$query = "UPDATE ejecutivo SET id_padre = " . ($id_padre ? "'$id_padre'" : "NULL") . ", id_pla = " . ($id_pla ? "'$id_pla'" : "NULL") . " WHERE id_eje = '$id_eje'";
			
			// Log de la query
			file_put_contents('debug_ejecutivos.log', '[' . date('Y-m-d H:i:s') . '] QUERY MOVER: ' . $query . "\n", FILE_APPEND);
			
			if(mysqli_query($connection, $query)) {
				// Verificar que el cambio se aplicó
				$queryVerificar = "SELECT id_padre, id_pla FROM ejecutivo WHERE id_eje = '$id_eje'";
				$resultVerificar = ejecutarConsulta($queryVerificar, $connection);
				
				if($resultVerificar && !empty($resultVerificar)) {
					$nuevoPadreReal = $resultVerificar[0]['id_padre'];
					$nuevoPlantelReal = $resultVerificar[0]['id_pla'];
					file_put_contents('debug_ejecutivos.log', '[' . date('Y-m-d H:i:s') . '] VERIFICACIÓN - Nuevo padre: ' . $nuevoPadreReal . ', Nuevo plantel: ' . $nuevoPlantelReal . "\n", FILE_APPEND);
				}
				
				// Registrar en historial si existe
				if(function_exists('registrarHistorial')) {
					$descripcionPadre = $id_padre ? "bajo ejecutivo ID $id_padre" : "como nodo raíz";
					$descripcionPlantel = $id_pla ? " en plantel ID $id_pla" : "";
					$descripcion = "Se movió el ejecutivo '$nombreEjecutivo' $descripcionPadre$descripcionPlantel";
					registrarHistorial($connection, 0, 'cambio', $descripcion, $nombreEjecutivo);
				}
				
				file_put_contents('debug_ejecutivos.log', '[' . date('Y-m-d H:i:s') . '] EJECUTIVO MOVIDO EXITOSAMENTE' . "\n", FILE_APPEND);
				file_put_contents('debug_ejecutivos.log', '[' . date('Y-m-d H:i:s') . '] RESPUESTA ENVIADA: SUCCESS' . "\n", FILE_APPEND);
				file_put_contents('debug_ejecutivos.log', '[' . date('Y-m-d H:i:s') . '] ==============================================' . "\n", FILE_APPEND);
				echo respuestaExito(null, 'Ejecutivo movido correctamente');
			} else {
				$error = mysqli_error($connection);
				file_put_contents('debug_ejecutivos.log', '[' . date('Y-m-d H:i:s') . '] ERROR AL MOVER: ' . $error . "\n", FILE_APPEND);
				file_put_contents('debug_ejecutivos.log', '[' . date('Y-m-d H:i:s') . '] RESPUESTA ENVIADA: ERROR' . "\n", FILE_APPEND);
				file_put_contents('debug_ejecutivos.log', '[' . date('Y-m-d H:i:s') . '] ==============================================' . "\n", FILE_APPEND);
				echo respuestaError('Error al mover ejecutivo: ' . $error);
			}
		break;

		case 'obtener_estadisticas':
			$queryStats = "SELECT 
							COUNT(*) as total,
							SUM(CASE WHEN eli_eje = 1 THEN 1 ELSE 0 END) as activos,
							SUM(CASE WHEN eli_eje = 0 THEN 1 ELSE 0 END) as ocultos,
							SUM(CASE WHEN id_padre IS NULL THEN 1 ELSE 0 END) as raiz
						   FROM ejecutivo";
			
			$stats = ejecutarConsulta($queryStats, $connection);
			
			if($stats !== false && !empty($stats)) {
				echo respuestaExito($stats[0], 'Estadísticas obtenidas correctamente');
			} else {
				echo respuestaError('Error al obtener estadísticas: ' . mysqli_error($connection));
			}
		break;

		case 'obtener_planteles':
			// Obtener todos los planteles
			$query = "SELECT id_pla, nom_pla, fec_pla FROM plantel ORDER BY nom_pla ASC";
			
			// Log para debugging
			file_put_contents('debug_ejecutivos.log', '[' . date('Y-m-d H:i:s') . '] Query planteles: ' . $query . "\n", FILE_APPEND);
			
			$datos = ejecutarConsulta($query, $connection);

			if($datos !== false) {
				file_put_contents('debug_ejecutivos.log', '[' . date('Y-m-d H:i:s') . '] Planteles encontrados: ' . count($datos) . "\n", FILE_APPEND);
				echo respuestaExito($datos, 'Planteles obtenidos correctamente');
			} else {
				$error = mysqli_error($connection);
				file_put_contents('debug_ejecutivos.log', '[' . date('Y-m-d H:i:s') . '] Error MySQL: ' . $error . "\n", FILE_APPEND);
				echo respuestaError('Error al consultar planteles: ' . $error);
			}
		break;

		case 'obtener_ejecutivos_por_plantel':
			// Obtener ejecutivos agrupados por plantel con jerarquía y planteles asociados
			$query = "SELECT e.id_eje, e.nom_eje, e.tel_eje, e.eli_eje, e.id_padre, e.id_pla, 
					         p.nom_pla,
					         GROUP_CONCAT(DISTINCT CONCAT(pa.id_pla, ':', pa.nom_pla) SEPARATOR '|') as planteles_asociados,
					         COUNT(DISTINCT pe.id_pla) as total_planteles_asociados
					  FROM ejecutivo e 
					  LEFT JOIN plantel p ON e.id_pla = p.id_pla 
					  LEFT JOIN planteles_ejecutivo pe ON e.id_eje = pe.id_eje
					  LEFT JOIN plantel pa ON pe.id_pla = pa.id_pla
					  GROUP BY e.id_eje
					  ORDER BY e.id_pla, e.eli_eje DESC, e.nom_eje ASC";
			
			// Log para debugging
			file_put_contents('debug_ejecutivos.log', '[' . date('Y-m-d H:i:s') . '] Query ejecutivos por plantel: ' . $query . "\n", FILE_APPEND);
			
			$datos = ejecutarConsulta($query, $connection);

			if($datos !== false) {
				// Procesar los datos para estructurar mejor los planteles asociados
				foreach($datos as &$ejecutivo) {
					$ejecutivo['planteles_asociados_array'] = [];
					if($ejecutivo['planteles_asociados']) {
						$planteles = explode('|', $ejecutivo['planteles_asociados']);
						foreach($planteles as $plantel) {
							$partes = explode(':', $plantel);
							if(count($partes) == 2) {
								$ejecutivo['planteles_asociados_array'][] = [
									'id_pla' => $partes[0],
									'nom_pla' => $partes[1]
								];
							}
						}
					}
				}
				
				file_put_contents('debug_ejecutivos.log', '[' . date('Y-m-d H:i:s') . '] Ejecutivos por plantel encontrados: ' . count($datos) . "\n", FILE_APPEND);
				echo respuestaExito($datos, 'Ejecutivos por plantel obtenidos correctamente');
			} else {
				$error = mysqli_error($connection);
				file_put_contents('debug_ejecutivos.log', '[' . date('Y-m-d H:i:s') . '] Error MySQL ejecutivos por plantel: ' . $error . "\n", FILE_APPEND);
				echo respuestaError('Error al consultar ejecutivos por plantel: ' . $error);
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
// FUNCIONES AUXILIARES
// =====================================

function esReferenciaCircular($connection, $hijo_id, $padre_id) {
	// Verificar si establecer padre_id como padre de hijo_id crearía una referencia circular
	
	$nodo_actual = $padre_id;
	$visitados = [];
	
	while($nodo_actual && !in_array($nodo_actual, $visitados)) {
		$visitados[] = $nodo_actual;
		
		// Si encontramos que el padre propuesto es descendiente del hijo, hay circularidad
		if($nodo_actual == $hijo_id) {
			return true;
		}
		
		// Obtener el padre del nodo actual
		$query = "SELECT id_padre FROM ejecutivo WHERE id_eje = '" . escape($nodo_actual, $connection) . "'";
		$result = ejecutarConsulta($query, $connection);
		
		if($result && !empty($result)) {
			$nodo_actual = $result[0]['id_padre'];
		} else {
			break;
		}
	}
	
	return false;
}

function obtenerDescendientes($connection, $id_eje) {
	// Función recursiva para obtener todos los descendientes de un ejecutivo
	$descendientes = [];
	
	$query = "SELECT id_eje, nom_eje FROM ejecutivo WHERE id_padre = '" . escape($id_eje, $connection) . "'";
	$hijos = ejecutarConsulta($query, $connection);
	
	if($hijos) {
		foreach($hijos as $hijo) {
			$descendientes[] = $hijo;
			$descendientes = array_merge($descendientes, obtenerDescendientes($connection, $hijo['id_eje']));
		}
	}
	
	return $descendientes;
}
?>
