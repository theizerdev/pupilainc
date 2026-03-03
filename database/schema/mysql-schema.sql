/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `active_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `active_sessions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `session_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `last_activity` timestamp NOT NULL,
  `login_at` timestamp NULL DEFAULT NULL,
  `logout_at` timestamp NULL DEFAULT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `active_sessions_user_id_session_id_index` (`user_id`,`session_id`),
  CONSTRAINT `active_sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `log_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint unsigned DEFAULT NULL,
  `causer_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `causer_id` bigint unsigned DEFAULT NULL,
  `properties` json DEFAULT NULL,
  `batch_uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject` (`subject_type`,`subject_id`),
  KEY `causer` (`causer_type`,`causer_id`),
  KEY `activity_log_log_name_index` (`log_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `anulacion_talonarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `anulacion_talonarios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `sucursal_id` bigint unsigned NOT NULL,
  `serie_id` bigint unsigned DEFAULT NULL,
  `tipo_documento` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `serie_afectada` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero_control_desde` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero_control_hasta` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `correlativo_desde` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `correlativo_hasta` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cantidad_documentos` int NOT NULL,
  `fecha_anulacion` date NOT NULL,
  `motivo` enum('dano_fisico','robo','extravio','error_impresion','cambio_datos_fiscales','fin_actividad','otro') COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion_motivo` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('registrado','reportado_seniat','confirmado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'registrado',
  `fecha_reporte_seniat` date DEFAULT NULL,
  `numero_reporte_seniat` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `acta_destruccion` tinyint(1) NOT NULL DEFAULT '0',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `anulacion_talonarios_sucursal_id_foreign` (`sucursal_id`),
  KEY `anulacion_talonarios_serie_id_foreign` (`serie_id`),
  KEY `anulacion_talonarios_user_id_foreign` (`user_id`),
  KEY `anulacion_talonarios_empresa_id_sucursal_id_tipo_documento_index` (`empresa_id`,`sucursal_id`,`tipo_documento`),
  CONSTRAINT `anulacion_talonarios_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `anulacion_talonarios_serie_id_foreign` FOREIGN KEY (`serie_id`) REFERENCES `series` (`id`) ON DELETE SET NULL,
  CONSTRAINT `anulacion_talonarios_sucursal_id_foreign` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`) ON DELETE CASCADE,
  CONSTRAINT `anulacion_talonarios_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `asientos_contables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asientos_contables` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha` date NOT NULL,
  `tipo` enum('apertura','diario','ajuste','cierre') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'diario',
  `descripcion` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('borrador','aprobado','anulado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  `referencia_tipo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referencia_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned NOT NULL,
  `empresa_id` bigint unsigned NOT NULL,
  `sucursal_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `asientos_contables_user_id_foreign` (`user_id`),
  KEY `asientos_contables_sucursal_id_foreign` (`sucursal_id`),
  KEY `asientos_contables_empresa_id_fecha_index` (`empresa_id`,`fecha`),
  KEY `asientos_contables_referencia_tipo_referencia_id_index` (`referencia_tipo`,`referencia_id`),
  KEY `asientos_contables_estado_fecha_index` (`estado`,`fecha`),
  CONSTRAINT `asientos_contables_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `asientos_contables_sucursal_id_foreign` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`) ON DELETE SET NULL,
  CONSTRAINT `asientos_contables_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `asientos_detalles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asientos_detalles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `asiento_id` bigint unsigned NOT NULL,
  `cuenta_id` bigint unsigned NOT NULL,
  `debe` decimal(15,2) NOT NULL DEFAULT '0.00',
  `haber` decimal(15,2) NOT NULL DEFAULT '0.00',
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `asientos_detalles_cuenta_id_foreign` (`cuenta_id`),
  KEY `asientos_detalles_asiento_id_cuenta_id_index` (`asiento_id`,`cuenta_id`),
  CONSTRAINT `asientos_detalles_asiento_id_foreign` FOREIGN KEY (`asiento_id`) REFERENCES `asientos_contables` (`id`) ON DELETE CASCADE,
  CONSTRAINT `asientos_detalles_cuenta_id_foreign` FOREIGN KEY (`cuenta_id`) REFERENCES `cuentas_contables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `auditable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `auditable_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `url` text COLLATE utf8mb4_unicode_ci,
  `method` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags` json DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_auditable_type_auditable_id_index` (`auditable_type`,`auditable_id`),
  KEY `audit_logs_user_id_index` (`user_id`),
  KEY `audit_logs_action_index` (`action`),
  KEY `audit_logs_created_at_index` (`created_at`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `baremos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `baremos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `sucursal_id` bigint unsigned DEFAULT NULL,
  `especialidad_id` bigint unsigned NOT NULL,
  `codigo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_servicio` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `costo_usd` decimal(10,2) NOT NULL,
  `costo_bs` decimal(10,2) DEFAULT NULL,
  `aplica_iva` tinyint(1) NOT NULL DEFAULT '1',
  `exento_iva` tinyint(1) NOT NULL DEFAULT '0',
  `duracion_minutos` int DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `baremos_codigo_unique` (`codigo`),
  KEY `baremos_sucursal_id_foreign` (`sucursal_id`),
  KEY `baremos_especialidad_id_foreign` (`especialidad_id`),
  KEY `baremos_empresa_id_especialidad_id_index` (`empresa_id`,`especialidad_id`),
  KEY `baremos_codigo_index` (`codigo`),
  KEY `baremos_activo_index` (`activo`),
  CONSTRAINT `baremos_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `baremos_especialidad_id_foreign` FOREIGN KEY (`especialidad_id`) REFERENCES `especialidades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `baremos_sucursal_id_foreign` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cajas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cajas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `sucursal_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `fecha` date NOT NULL,
  `numero_corte` int NOT NULL DEFAULT '1',
  `monto_inicial` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_efectivo` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_transferencias` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_tarjetas` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_ingresos` decimal(10,2) NOT NULL DEFAULT '0.00',
  `monto_final` decimal(10,2) NOT NULL DEFAULT '0.00',
  `estado` enum('abierta','cerrada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'abierta',
  `fecha_apertura` timestamp NOT NULL,
  `fecha_cierre` timestamp NULL DEFAULT NULL,
  `observaciones_apertura` text COLLATE utf8mb4_unicode_ci,
  `observaciones_cierre` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cajas_user_id_foreign` (`user_id`),
  KEY `cajas_fecha_estado_index` (`fecha`,`estado`),
  KEY `cajas_empresa_id_foreign` (`empresa_id`),
  KEY `cajas_sucursal_id_foreign` (`sucursal_id`),
  CONSTRAINT `cajas_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cajas_sucursal_id_foreign` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cajas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `chat_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chat_messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sender_id` bigint unsigned NOT NULL,
  `receiver_id` bigint unsigned NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `read_at` timestamp NULL DEFAULT NULL,
  `empresa_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chat_messages_sender_id_receiver_id_index` (`sender_id`,`receiver_id`),
  KEY `chat_messages_receiver_id_is_read_index` (`receiver_id`,`is_read`),
  KEY `chat_messages_empresa_id_index` (`empresa_id`),
  CONSTRAINT `chat_messages_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_messages_receiver_id_foreign` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cita_confirmaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cita_confirmaciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cita_id` bigint unsigned NOT NULL,
  `metodo` enum('whatsapp','email','sms') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'whatsapp',
  `destinatario` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mensaje_enviado` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token_confirmacion` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('pendiente','confirmado','rechazado','sin_respuesta','expirado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `respuesta_recibida` text COLLATE utf8mb4_unicode_ci,
  `fecha_envio` timestamp NOT NULL,
  `fecha_respuesta` timestamp NULL DEFAULT NULL,
  `intentos` int NOT NULL DEFAULT '0',
  `max_intentos` int NOT NULL DEFAULT '3',
  `empresa_id` bigint unsigned DEFAULT NULL,
  `sucursal_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cita_confirmaciones_token_confirmacion_unique` (`token_confirmacion`),
  KEY `cita_confirmaciones_empresa_id_foreign` (`empresa_id`),
  KEY `cita_confirmaciones_sucursal_id_foreign` (`sucursal_id`),
  KEY `cita_confirmaciones_created_by_foreign` (`created_by`),
  KEY `cita_confirmaciones_cita_id_estado_index` (`cita_id`,`estado`),
  KEY `cita_confirmaciones_estado_fecha_envio_index` (`estado`,`fecha_envio`),
  KEY `cita_confirmaciones_token_confirmacion_index` (`token_confirmacion`),
  CONSTRAINT `cita_confirmaciones_cita_id_foreign` FOREIGN KEY (`cita_id`) REFERENCES `citas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cita_confirmaciones_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cita_confirmaciones_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cita_confirmaciones_sucursal_id_foreign` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cita_recordatorios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cita_recordatorios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cita_id` bigint unsigned NOT NULL,
  `tipo` enum('24h','2h','personalizado') COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_envio_programado` datetime NOT NULL,
  `fecha_envio_real` datetime DEFAULT NULL,
  `estado` enum('pendiente','enviado','fallido') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `canal` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'whatsapp',
  `intentos` smallint unsigned NOT NULL DEFAULT '0',
  `mensaje` text COLLATE utf8mb4_unicode_ci,
  `error_mensaje` text COLLATE utf8mb4_unicode_ci,
  `confirmacion_respuesta` tinyint(1) DEFAULT NULL,
  `fecha_respuesta` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cita_recordatorios_fecha_envio_programado_estado_index` (`fecha_envio_programado`,`estado`),
  KEY `cita_recordatorios_cita_id_index` (`cita_id`),
  CONSTRAINT `cita_recordatorios_cita_id_foreign` FOREIGN KEY (`cita_id`) REFERENCES `citas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `citas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `citas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `paciente_id` bigint unsigned NOT NULL,
  `medico_id` bigint unsigned NOT NULL,
  `especialidad_id` bigint unsigned DEFAULT NULL,
  `subespecialidad_id` bigint unsigned DEFAULT NULL,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime NOT NULL,
  `motivo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('pendiente','confirmada','en_curso','completada','cancelada','no_asistio') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `estado_preconsulta` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `token_preconsulta` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_envio_preconsulta` timestamp NULL DEFAULT NULL,
  `fecha_completado_preconsulta` timestamp NULL DEFAULT NULL,
  `motivo_cancelacion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cancelado_por` bigint unsigned DEFAULT NULL,
  `tipo_consulta_id` bigint unsigned DEFAULT NULL,
  `notas` text COLLATE utf8mb4_unicode_ci,
  `color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `empresa_id` bigint unsigned NOT NULL,
  `sucursal_id` bigint unsigned NOT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `citas_token_preconsulta_unique` (`token_preconsulta`),
  KEY `citas_sucursal_id_foreign` (`sucursal_id`),
  KEY `citas_created_by_foreign` (`created_by`),
  KEY `citas_medico_id_fecha_inicio_fecha_fin_index` (`medico_id`,`fecha_inicio`,`fecha_fin`),
  KEY `citas_paciente_id_fecha_inicio_index` (`paciente_id`,`fecha_inicio`),
  KEY `citas_estado_index` (`estado`),
  KEY `citas_empresa_id_sucursal_id_index` (`empresa_id`,`sucursal_id`),
  KEY `citas_especialidad_id_foreign` (`especialidad_id`),
  KEY `citas_subespecialidad_id_foreign` (`subespecialidad_id`),
  KEY `citas_tipo_consulta_id_index` (`tipo_consulta_id`),
  KEY `citas_cancelado_por_foreign` (`cancelado_por`),
  KEY `citas_empresa_id_medico_id_fecha_inicio_index` (`empresa_id`,`medico_id`,`fecha_inicio`),
  KEY `citas_estado_preconsulta_index` (`estado_preconsulta`),
  KEY `citas_token_preconsulta_index` (`token_preconsulta`),
  CONSTRAINT `citas_cancelado_por_foreign` FOREIGN KEY (`cancelado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `citas_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `citas_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `citas_especialidad_id_foreign` FOREIGN KEY (`especialidad_id`) REFERENCES `especialidades` (`id`) ON DELETE SET NULL,
  CONSTRAINT `citas_medico_id_foreign` FOREIGN KEY (`medico_id`) REFERENCES `medicos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `citas_paciente_id_foreign` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `citas_subespecialidad_id_foreign` FOREIGN KEY (`subespecialidad_id`) REFERENCES `subespecialidades` (`id`) ON DELETE SET NULL,
  CONSTRAINT `citas_sucursal_id_foreign` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`) ON DELETE CASCADE,
  CONSTRAINT `citas_tipo_consulta_id_foreign` FOREIGN KEY (`tipo_consulta_id`) REFERENCES `tipo_consultas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `clientes_fiscales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clientes_fiscales` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `sucursal_id` bigint unsigned NOT NULL,
  `paciente_id` bigint unsigned DEFAULT NULL,
  `tipo_documento` enum('V','J','E','G','P') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'V',
  `numero_documento` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `razon_social` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_comercial` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion_fiscal` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ciudad` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigo_postal` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cliente_fiscal_unique` (`empresa_id`,`tipo_documento`,`numero_documento`),
  KEY `clientes_fiscales_paciente_id_index` (`paciente_id`),
  KEY `clientes_fiscales_sucursal_id_foreign` (`sucursal_id`),
  CONSTRAINT `clientes_fiscales_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `clientes_fiscales_paciente_id_foreign` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `clientes_fiscales_sucursal_id_foreign` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `conceptos_pago`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `conceptos_pago` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `sucursal_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `conceptos_pago_empresa_id_foreign` (`empresa_id`),
  KEY `conceptos_pago_sucursal_id_foreign` (`sucursal_id`),
  CONSTRAINT `conceptos_pago_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `conceptos_pago_sucursal_id_foreign` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `consulta_diagnostico`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `consulta_diagnostico` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `consulta_id` bigint unsigned NOT NULL,
  `diagnostico_id` bigint unsigned NOT NULL,
  `tipo` enum('principal','secundario') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'secundario',
  `orden` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `consulta_diagnostico_diagnostico_id_foreign` (`diagnostico_id`),
  KEY `consulta_diagnostico_consulta_id_tipo_index` (`consulta_id`,`tipo`),
  CONSTRAINT `consulta_diagnostico_consulta_id_foreign` FOREIGN KEY (`consulta_id`) REFERENCES `consultas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `consulta_diagnostico_diagnostico_id_foreign` FOREIGN KEY (`diagnostico_id`) REFERENCES `diagnosticos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `consulta_estudios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `consulta_estudios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `consulta_id` bigint unsigned NOT NULL,
  `tipo_estudio` enum('imagen','laboratorio','otros') COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_estudio` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `indicaciones` text COLLATE utf8mb4_unicode_ci,
  `orden` int NOT NULL DEFAULT '0',
  `empresa_id` bigint unsigned NOT NULL,
  `sucursal_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `consulta_estudios_empresa_id_foreign` (`empresa_id`),
  KEY `consulta_estudios_sucursal_id_foreign` (`sucursal_id`),
  KEY `consulta_estudios_created_by_foreign` (`created_by`),
  KEY `consulta_estudios_updated_by_foreign` (`updated_by`),
  KEY `consulta_estudios_consulta_id_tipo_estudio_index` (`consulta_id`,`tipo_estudio`),
  CONSTRAINT `consulta_estudios_consulta_id_foreign` FOREIGN KEY (`consulta_id`) REFERENCES `consultas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `consulta_estudios_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `consulta_estudios_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `consulta_estudios_sucursal_id_foreign` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`),
  CONSTRAINT `consulta_estudios_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `consulta_evaluaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `consulta_evaluaciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `consulta_id` bigint unsigned NOT NULL,
  `enfermedad_actual` text COLLATE utf8mb4_unicode_ci,
  `examen_fisico` text COLLATE utf8mb4_unicode_ci,
  `conclusion` text COLLATE utf8mb4_unicode_ci,
  `observaciones_adicionales` text COLLATE utf8mb4_unicode_ci,
  `empresa_id` bigint unsigned NOT NULL,
  `sucursal_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `consulta_evaluaciones_empresa_id_foreign` (`empresa_id`),
  KEY `consulta_evaluaciones_sucursal_id_foreign` (`sucursal_id`),
  KEY `consulta_evaluaciones_created_by_foreign` (`created_by`),
  KEY `consulta_evaluaciones_updated_by_foreign` (`updated_by`),
  KEY `consulta_evaluaciones_consulta_id_index` (`consulta_id`),
  CONSTRAINT `consulta_evaluaciones_consulta_id_foreign` FOREIGN KEY (`consulta_id`) REFERENCES `consultas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `consulta_evaluaciones_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `consulta_evaluaciones_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `consulta_evaluaciones_sucursal_id_foreign` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`),
  CONSTRAINT `consulta_evaluaciones_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `consulta_tratamientos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `consulta_tratamientos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `consulta_id` bigint unsigned NOT NULL,
  `medicamento` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `indicaciones` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `orden` int NOT NULL DEFAULT '0',
  `empresa_id` bigint unsigned NOT NULL,
  `sucursal_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `consulta_tratamientos_empresa_id_foreign` (`empresa_id`),
  KEY `consulta_tratamientos_sucursal_id_foreign` (`sucursal_id`),
  KEY `consulta_tratamientos_created_by_foreign` (`created_by`),
  KEY `consulta_tratamientos_updated_by_foreign` (`updated_by`),
  KEY `consulta_tratamientos_consulta_id_index` (`consulta_id`),
  CONSTRAINT `consulta_tratamientos_consulta_id_foreign` FOREIGN KEY (`consulta_id`) REFERENCES `consultas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `consulta_tratamientos_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `consulta_tratamientos_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `consulta_tratamientos_sucursal_id_foreign` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`),
  CONSTRAINT `consulta_tratamientos_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `consultas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `consultas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cita_id` bigint unsigned DEFAULT NULL,
  `paciente_id` bigint unsigned NOT NULL,
  `medico_id` bigint unsigned NOT NULL,
  `especialidad_id` bigint unsigned DEFAULT NULL,
  `fecha_consulta` datetime NOT NULL,
  `preconsulta` tinyint(1) NOT NULL DEFAULT '0',
  `motivo_consulta` text COLLATE utf8mb4_unicode_ci,
  `estado` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sala_espera',
  `estado_changed_at` timestamp NULL DEFAULT NULL,
  `empresa_id` bigint unsigned NOT NULL,
  `sucursal_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `consultas_codigo_unique` (`codigo`),
  KEY `consultas_paciente_id_foreign` (`paciente_id`),
  KEY `consultas_especialidad_id_foreign` (`especialidad_id`),
  KEY `consultas_sucursal_id_foreign` (`sucursal_id`),
  KEY `consultas_created_by_foreign` (`created_by`),
  KEY `consultas_updated_by_foreign` (`updated_by`),
  KEY `consultas_empresa_id_sucursal_id_index` (`empresa_id`,`sucursal_id`),
  KEY `consultas_medico_id_estado_index` (`medico_id`,`estado`),
  KEY `consultas_cita_id_index` (`cita_id`),
  CONSTRAINT `consultas_cita_id_foreign` FOREIGN KEY (`cita_id`) REFERENCES `citas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `consultas_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `consultas_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `consultas_especialidad_id_foreign` FOREIGN KEY (`especialidad_id`) REFERENCES `especialidades` (`id`),
  CONSTRAINT `consultas_medico_id_foreign` FOREIGN KEY (`medico_id`) REFERENCES `medicos` (`id`),
  CONSTRAINT `consultas_paciente_id_foreign` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `consultas_sucursal_id_foreign` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`),
  CONSTRAINT `consultas_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `consultorio_asignaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `consultorio_asignaciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `consultorio_id` bigint unsigned NOT NULL,
  `medico_id` bigint unsigned NOT NULL,
  `fecha` date NOT NULL,
  `hora_inicio` time DEFAULT NULL,
  `hora_fin` time DEFAULT NULL,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `sucursal_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `consultorio_asignaciones_empresa_id_foreign` (`empresa_id`),
  KEY `consultorio_asignaciones_sucursal_id_foreign` (`sucursal_id`),
  KEY `consultorio_asignaciones_consultorio_id_foreign` (`consultorio_id`),
  KEY `consultorio_asignaciones_created_by_foreign` (`created_by`),
  KEY `consultorio_asignaciones_fecha_empresa_id_sucursal_id_index` (`fecha`,`empresa_id`,`sucursal_id`),
  KEY `consultorio_asignaciones_medico_id_fecha_index` (`medico_id`,`fecha`),
  CONSTRAINT `consultorio_asignaciones_consultorio_id_foreign` FOREIGN KEY (`consultorio_id`) REFERENCES `consultorios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `consultorio_asignaciones_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `consultorio_asignaciones_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `consultorio_asignaciones_medico_id_foreign` FOREIGN KEY (`medico_id`) REFERENCES `medicos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `consultorio_asignaciones_sucursal_id_foreign` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `consultorios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `consultorios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ubicacion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `empresa_id` bigint unsigned DEFAULT NULL,
  `sucursal_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `consultorios_empresa_id_foreign` (`empresa_id`),
  KEY `consultorios_sucursal_id_foreign` (`sucursal_id`),
  CONSTRAINT `consultorios_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `consultorios_sucursal_id_foreign` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cuentas_contables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cuentas_contables` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('activo','pasivo','patrimonio','ingreso','egreso','costo') COLLATE utf8mb4_unicode_ci NOT NULL,
  `naturaleza` enum('deudora','acreedora') COLLATE utf8mb4_unicode_ci NOT NULL,
  `nivel` int NOT NULL DEFAULT '1',
  `cuenta_padre_id` bigint unsigned DEFAULT NULL,
  `acepta_movimientos` tinyint(1) NOT NULL DEFAULT '1',
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `empresa_id` bigint unsigned NOT NULL,
  `sucursal_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cuentas_contables_codigo_unique` (`codigo`),
  KEY `cuentas_contables_cuenta_padre_id_foreign` (`cuenta_padre_id`),
  KEY `cuentas_contables_sucursal_id_foreign` (`sucursal_id`),
  KEY `cuentas_contables_empresa_id_activo_index` (`empresa_id`,`activo`),
  KEY `cuentas_contables_tipo_activo_index` (`tipo`,`activo`),
  CONSTRAINT `cuentas_contables_cuenta_padre_id_foreign` FOREIGN KEY (`cuenta_padre_id`) REFERENCES `cuentas_contables` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cuentas_contables_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cuentas_contables_sucursal_id_foreign` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cuestionarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cuestionarios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `tipo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'preconsulta',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `empresa_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cuestionarios_empresa_id_activo_index` (`empresa_id`,`activo`),
  CONSTRAINT `cuestionarios_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `diagnosticos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `diagnosticos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `empresa_id` bigint unsigned NOT NULL,
  `sucursal_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `diagnosticos_codigo_unique` (`codigo`),
  KEY `diagnosticos_created_by_foreign` (`created_by`),
  KEY `diagnosticos_codigo_activo_index` (`codigo`,`activo`),
  KEY `diagnosticos_empresa_id_index` (`empresa_id`),
  KEY `diagnosticos_sucursal_id_index` (`sucursal_id`),
  CONSTRAINT `diagnosticos_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `diagnosticos_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `diagnosticos_sucursal_id_foreign` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `empresas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empresas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pais_id` bigint unsigned DEFAULT NULL,
  `razon_social` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `documento` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rif_fiscal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` text COLLATE utf8mb4_unicode_ci,
  `direccion_fiscal` text COLLATE utf8mb4_unicode_ci,
  `ciudad_fiscal` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado_fiscal` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigo_postal_fiscal` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion_fiscal_completa` text COLLATE utf8mb4_unicode_ci,
  `latitud` decimal(10,8) DEFAULT NULL,
  `longitud` decimal(11,8) DEFAULT NULL,
  `representante_legal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono_fiscal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `punto_emision` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0001',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `correo_fiscal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `api_key` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp_api_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp_rate_limit` int NOT NULL DEFAULT '100',
  `whatsapp_active` tinyint(1) NOT NULL DEFAULT '1',
  `whatsapp_phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp_status` enum('disconnected','connecting','connected','qr_ready') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'disconnected' COMMENT 'Estado de conexión de WhatsApp',
  `whatsapp_last_connected` timestamp NULL DEFAULT NULL COMMENT 'Última vez que se conectó WhatsApp',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `numero_control_desde` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_control_hasta` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_autorizacion_seniat` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `empresas_documento_unique` (`documento`),
  UNIQUE KEY `empresas_api_key_unique` (`api_key`),
  KEY `empresas_pais_id_foreign` (`pais_id`),
  KEY `empresas_rif_fiscal_index` (`rif_fiscal`),
  CONSTRAINT `empresas_pais_id_foreign` FOREIGN KEY (`pais_id`) REFERENCES `pais` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `enfermero_especialidades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `enfermero_especialidades` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `enfermero_id` bigint unsigned NOT NULL,
  `especialidad` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `sucursal_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `enfermero_especialidades_sucursal_id_foreign` (`sucursal_id`),
  KEY `enfermero_especialidades_enfermero_id_especialidad_index` (`enfermero_id`,`especialidad`),
  KEY `enfermero_especialidades_empresa_id_sucursal_id_index` (`empresa_id`,`sucursal_id`),
  CONSTRAINT `enfermero_especialidades_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `enfermero_especialidades_enfermero_id_foreign` FOREIGN KEY (`enfermero_id`) REFERENCES `enfermeros` (`id`) ON DELETE CASCADE,
  CONSTRAINT `enfermero_especialidades_sucursal_id_foreign` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `enfermero_horarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `enfermero_horarios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `enfermero_id` bigint unsigned NOT NULL,
  `dia_semana` int NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `duracion_cita` int NOT NULL DEFAULT '30',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `empresa_id` bigint unsigned DEFAULT NULL,
  `sucursal_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `enfermero_horarios_enfermero_id_dia_semana_unique` (`enfermero_id`,`dia_semana`),
  KEY `enfermero_horarios_sucursal_id_foreign` (`sucursal_id`),
  KEY `enfermero_horarios_empresa_id_sucursal_id_index` (`empresa_id`,`sucursal_id`),
  KEY `enfermero_horarios_activo_index` (`activo`),
  CONSTRAINT `enfermero_horarios_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `enfermero_horarios_enfermero_id_foreign` FOREIGN KEY (`enfermero_id`) REFERENCES `enfermeros` (`id`) ON DELETE CASCADE,
  CONSTRAINT `enfermero_horarios_sucursal_id_foreign` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `enfermeros`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `enfermeros` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `nombres` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `documento_identidad` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `genero` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` text COLLATE utf8mb4_unicode_ci,
  `licencia_enfermeria` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `anios_experiencia` int NOT NULL DEFAULT '0',
  `nivel_experiencia` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_enfermero` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `especialidad_enfermeria` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `empresa_id` bigint unsigned DEFAULT NULL,
  `sucursal_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `enfermeros_user_id_unique` (`user_id`),
  UNIQUE KEY `enfermeros_documento_identidad_unique` (`documento_identidad`),
  UNIQUE KEY `enfermeros_licencia_enfermeria_unique` (`licencia_enfermeria`),
  KEY `enfermeros_sucursal_id_foreign` (`sucursal_id`),
  KEY `enfermeros_empresa_id_sucursal_id_index` (`empresa_id`,`sucursal_id`),
  KEY `enfermeros_status_index` (`status`),
  KEY `enfermeros_tipo_enfermero_index` (`tipo_enfermero`),
  KEY `enfermeros_nivel_experiencia_index` (`nivel_experiencia`),
  CONSTRAINT `enfermeros_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `enfermeros_sucursal_id_foreign` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`) ON DELETE SET NULL,
  CONSTRAINT `enfermeros_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `especialidades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `especialidades` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `codigo` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#3B82F6',
  `icono` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fa-stethoscope',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `empresa_id` bigint unsigned NOT NULL,
  `sucursal_id` bigint unsigned NOT NULL,
  `costo_consulta` decimal(10,2) NOT NULL DEFAULT '0.00',
  `duracion_consulta` int NOT NULL DEFAULT '30',
  `requiere_cita_previa` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `especialidades_codigo_unique` (`codigo`),
  KEY `especialidades_sucursal_id_foreign` (`sucursal_id`),
  KEY `especialidades_empresa_id_sucursal_id_index` (`empresa_id`,`sucursal_id`),
  KEY `especialidades_status_index` (`status`),
  KEY `especialidades_codigo_index` (`codigo`),
  CONSTRAINT `especialidades_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `especialidades_sucursal_id_foreign` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `exchange_rate_daily_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exchange_rate_daily_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `monthly_history_id` bigint unsigned NOT NULL,
  `date` date NOT NULL,
  `usd_rate` decimal(10,4) NOT NULL,
  `eur_rate` decimal(10,4) DEFAULT NULL,
  `source` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fetch_time` time DEFAULT NULL,
  `recorded_at` timestamp NULL DEFAULT NULL,
  `recorded_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `exchange_rate_daily_histories_monthly_history_id_date_unique` (`monthly_history_id`,`date`),
  CONSTRAINT `exchange_rate_daily_histories_monthly_history_id_foreign` FOREIGN KEY (`monthly_history_id`) REFERENCES `exchange_rate_monthly_histories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `exchange_rate_monthly_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exchange_rate_monthly_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `year` smallint unsigned NOT NULL,
  `month` tinyint unsigned NOT NULL,
  `usd_avg` decimal(10,4) NOT NULL,
  `usd_min` decimal(10,4) NOT NULL,
  `usd_max` decimal(10,4) NOT NULL,
  `eur_avg` decimal(10,4) DEFAULT NULL,
  `eur_min` decimal(10,4) DEFAULT NULL,
  `eur_max` decimal(10,4) DEFAULT NULL,
  `records_count` int unsigned NOT NULL,
  `sources` json DEFAULT NULL,
  `daily_records` json DEFAULT NULL,
  `generated_at` timestamp NULL DEFAULT NULL,
  `generated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `exchange_rate_monthly_histories_year_month_unique` (`year`,`month`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `exchange_rates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exchange_rates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `usd_rate` decimal(10,4) NOT NULL,
  `eur_rate` decimal(10,4) DEFAULT NULL,
  `source` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fetch_time` time NOT NULL,
  `raw_data` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `exchange_rates_date_fetch_time_unique` (`date`,`fetch_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fiscal_control_sequences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fiscal_control_sequences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `sucursal_id` bigint unsigned NOT NULL,
  `correlativo_actual` bigint NOT NULL DEFAULT '0',
  `longitud` int NOT NULL DEFAULT '8',
  `prefijo` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rango_inicio` bigint DEFAULT NULL,
  `rango_fin` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fiscal_control_sequences_empresa_id_sucursal_id_unique` (`empresa_id`,`sucursal_id`),
  KEY `fiscal_control_sequences_sucursal_id_foreign` (`sucursal_id`),
  CONSTRAINT `fiscal_control_sequences_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fiscal_control_sequences_sucursal_id_foreign` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `impuestos_configuracion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `impuestos_configuracion` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `sucursal_id` bigint unsigned NOT NULL,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('porcentaje','fijo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'porcentaje',
  `porcentaje` decimal(5,2) NOT NULL DEFAULT '0.00',
  `monto_fijo` decimal(10,2) DEFAULT NULL,
  `aplica_servicios` tinyint(1) NOT NULL DEFAULT '1',
  `aplica_productos` tinyint(1) NOT NULL DEFAULT '1',
  `metodos_pago_aplicables` json DEFAULT NULL,
  `coletilla_fiscal` text COLLATE utf8mb4_unicode_ci,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `orden` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `impuestos_configuracion_empresa_id_codigo_unique` (`empresa_id`,`codigo`),
  KEY `impuestos_configuracion_sucursal_id_foreign` (`sucursal_id`),
  CONSTRAINT `impuestos_configuracion_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `impuestos_configuracion_sucursal_id_foreign` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `late_payment_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `late_payment_rules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('porcentaje','monto_fijo') COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` decimal(8,2) NOT NULL,
  `dias_gracia` int NOT NULL DEFAULT '0',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `empresa_id` bigint unsigned NOT NULL,
  `sucursal_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `late_payment_rules_empresa_id_foreign` (`empresa_id`),
  KEY `late_payment_rules_sucursal_id_foreign` (`sucursal_id`),
  CONSTRAINT `late_payment_rules_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `late_payment_rules_sucursal_id_foreign` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `medico_especialidad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `medico_especialidad` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `medico_id` bigint unsigned NOT NULL,
  `especialidad_id` bigint unsigned NOT NULL,
  `tarifa_consulta` decimal(10,2) DEFAULT NULL,
  `horario_atencion` json DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `medico_especialidad_medico_id_especialidad_id_unique` (`medico_id`,`especialidad_id`),
  KEY `medico_especialidad_medico_id_status_index` (`medico_id`,`status`),
  KEY `medico_especialidad_especialidad_id_status_index` (`especialidad_id`,`status`),
  CONSTRAINT `medico_especialidad_especialidad_id_foreign` FOREIGN KEY (`especialidad_id`) REFERENCES `especialidades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `medico_especialidad_medico_id_foreign` FOREIGN KEY (`medico_id`) REFERENCES `medicos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `medico_horarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `medico_horarios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `medico_id` bigint unsigned NOT NULL,
  `dia_semana` tinyint NOT NULL COMMENT '1=Lunes, 2=Martes, ..., 7=Domingo',
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `duracion_cita` int NOT NULL DEFAULT '30' COMMENT 'Duración de cada cita en minutos',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `fecha_inicio` date DEFAULT NULL COMMENT 'Fecha desde cuando aplica este horario',
  `fecha_fin` date DEFAULT NULL COMMENT 'Fecha hasta cuando aplica este horario',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `medico_horarios_medico_id_dia_semana_unique` (`medico_id`,`dia_semana`),
  KEY `medico_horarios_medico_id_dia_semana_activo_index` (`medico_id`,`dia_semana`,`activo`),
  KEY `medico_horarios_medico_id_activo_index` (`medico_id`,`activo`),
  CONSTRAINT `medico_horarios_medico_id_foreign` FOREIGN KEY (`medico_id`) REFERENCES `medicos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `medico_subespecialidad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `medico_subespecialidad` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `medico_id` bigint unsigned NOT NULL,
  `subespecialidad_id` bigint unsigned NOT NULL,
  `tarifa_consulta` decimal(10,2) DEFAULT NULL,
  `experiencia_anios` int NOT NULL DEFAULT '0',
  `nivel_experiencia` enum('Básico','Intermedio','Avanzado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Básico',
  `horario_atencion` json DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `medico_subespecialidad_medico_id_subespecialidad_id_unique` (`medico_id`,`subespecialidad_id`),
  KEY `medico_subespecialidad_medico_id_status_index` (`medico_id`,`status`),
  KEY `medico_subespecialidad_subespecialidad_id_status_index` (`subespecialidad_id`,`status`),
  CONSTRAINT `medico_subespecialidad_medico_id_foreign` FOREIGN KEY (`medico_id`) REFERENCES `medicos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `medico_subespecialidad_subespecialidad_id_foreign` FOREIGN KEY (`subespecialidad_id`) REFERENCES `subespecialidades` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `medicos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `medicos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `nombres` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `genero` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `documento_identidad` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` text COLLATE utf8mb4_unicode_ci,
  `licencia_medica` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `anios_experiencia` int NOT NULL DEFAULT '0',
  `nivel_experiencia` enum('Básico','Intermedio','Avanzado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Básico',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `empresa_id` bigint unsigned NOT NULL,
  `sucursal_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `medicos_documento_identidad_unique` (`documento_identidad`),
  UNIQUE KEY `medicos_licencia_medica_unique` (`licencia_medica`),
  KEY `medicos_user_id_foreign` (`user_id`),
  KEY `medicos_sucursal_id_foreign` (`sucursal_id`),
  KEY `medicos_empresa_id_sucursal_id_index` (`empresa_id`,`sucursal_id`),
  KEY `medicos_status_index` (`status`),
  KEY `medicos_documento_identidad_index` (`documento_identidad`),
  KEY `medicos_licencia_medica_index` (`licencia_medica`),
  CONSTRAINT `medicos_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `medicos_sucursal_id_foreign` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`) ON DELETE CASCADE,
  CONSTRAINT `medicos_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` json DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_read_at_index` (`user_id`,`read_at`),
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pacientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pacientes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombres` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `documento_identidad` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` text COLLATE utf8mb4_unicode_ci,
  `nickname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_nacimiento` date NOT NULL,
  `genero` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado_civil` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ocupacion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nacionalidad` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `empresa_id` bigint unsigned NOT NULL,
  `sucursal_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pacientes_empresa_id_documento_identidad_unique` (`empresa_id`,`documento_identidad`),
  KEY `pacientes_sucursal_id_foreign` (`sucursal_id`),
  KEY `pacientes_empresa_id_sucursal_id_index` (`empresa_id`,`sucursal_id`),
  KEY `pacientes_status_index` (`status`),
  KEY `pacientes_documento_identidad_index` (`documento_identidad`),
  CONSTRAINT `pacientes_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pacientes_sucursal_id_foreign` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pago_detalles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pago_detalles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pago_id` bigint unsigned NOT NULL,
  `concepto_pago_id` bigint unsigned DEFAULT NULL,
  `baremo_id` bigint unsigned DEFAULT NULL,
  `payment_schedule_id` bigint unsigned DEFAULT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cantidad` decimal(10,2) NOT NULL DEFAULT '1.00',
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `aplica_iva` tinyint(1) NOT NULL DEFAULT '1',
  `exento_iva` tinyint(1) NOT NULL DEFAULT '0',
  `iva_alicuota` decimal(5,2) NOT NULL DEFAULT '16.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pago_detalles_pago_id_foreign` (`pago_id`),
  KEY `pago_detalles_concepto_pago_id_foreign` (`concepto_pago_id`),
  KEY `pago_detalles_baremo_id_foreign` (`baremo_id`),
  CONSTRAINT `pago_detalles_baremo_id_foreign` FOREIGN KEY (`baremo_id`) REFERENCES `baremos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pago_detalles_concepto_pago_id_foreign` FOREIGN KEY (`concepto_pago_id`) REFERENCES `conceptos_pago` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pago_detalles_pago_id_foreign` FOREIGN KEY (`pago_id`) REFERENCES `pagos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pagos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pagos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `consulta_id` bigint unsigned DEFAULT NULL,
  `pago_origen_id` bigint unsigned DEFAULT NULL,
  `tipo_nota_credito_id` bigint unsigned DEFAULT NULL,
  `tipo_nota_debito_id` bigint unsigned DEFAULT NULL,
  `caja_id` bigint unsigned DEFAULT NULL,
  `serie_id` bigint unsigned DEFAULT NULL,
  `serie` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero` int NOT NULL,
  `numero_control_fiscal` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_pago` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `seniat_tipo_documento` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `es_factura_fiscal` tinyint(1) NOT NULL DEFAULT '0',
  `fecha` date NOT NULL,
  `fecha_emision_fiscal` date DEFAULT NULL,
  `fecha_vencimiento_fiscal` date DEFAULT NULL,
  `user_id` bigint unsigned NOT NULL,
  `cliente_fiscal_id` bigint unsigned DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `base_imponible` decimal(10,2) NOT NULL DEFAULT '0.00',
  `base_imponible_general` decimal(10,2) NOT NULL DEFAULT '0.00',
  `iva_monto_general` decimal(10,2) NOT NULL DEFAULT '0.00',
  `base_imponible_reducida` decimal(10,2) NOT NULL DEFAULT '0.00',
  `iva_monto_reducida` decimal(10,2) NOT NULL DEFAULT '0.00',
  `monto_exento` decimal(10,2) NOT NULL DEFAULT '0.00',
  `iva_porcentaje` decimal(5,2) NOT NULL DEFAULT '0.00',
  `iva_monto` decimal(10,2) NOT NULL DEFAULT '0.00',
  `igtf_porcentaje` decimal(5,2) NOT NULL DEFAULT '0.00',
  `igtf_monto` decimal(10,2) NOT NULL DEFAULT '0.00',
  `aplica_igtf` tinyint(1) NOT NULL DEFAULT '0',
  `descuento` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_con_impuestos` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tasa_cambio_usd` decimal(10,4) DEFAULT NULL,
  `tasa_cambio_eur` decimal(10,4) DEFAULT NULL,
  `total_usd` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_bs` decimal(10,2) NOT NULL DEFAULT '0.00',
  `metodo_pago` enum('efectivo_bs','efectivo_usd','transferencia_bs','transferencia_usd','pago_movil','zelle','paypal','mixto') COLLATE utf8mb4_unicode_ci NOT NULL,
  `condicion_pago` enum('contado','credito') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'contado',
  `referencia` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `es_pago_mixto` tinyint(1) NOT NULL DEFAULT '0',
  `detalles_pago_mixto` json DEFAULT NULL,
  `estado` enum('pendiente','aprobado','cancelado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `motivo_nota` text COLLATE utf8mb4_unicode_ci,
  `empresa_id` bigint unsigned NOT NULL,
  `sucursal_id` bigint unsigned NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pagos_consulta_id_foreign` (`consulta_id`),
  KEY `pagos_caja_id_foreign` (`caja_id`),
  KEY `pagos_serie_id_foreign` (`serie_id`),
  KEY `pagos_user_id_foreign` (`user_id`),
  KEY `pagos_sucursal_id_foreign` (`sucursal_id`),
  KEY `pagos_empresa_id_sucursal_id_index` (`empresa_id`,`sucursal_id`),
  KEY `pagos_fecha_index` (`fecha`),
  KEY `pagos_estado_index` (`estado`),
  KEY `pagos_metodo_pago_index` (`metodo_pago`),
  KEY `pagos_numero_control_fiscal_index` (`numero_control_fiscal`),
  KEY `pagos_es_factura_fiscal_index` (`es_factura_fiscal`),
  KEY `pagos_cliente_fiscal_id_index` (`cliente_fiscal_id`),
  KEY `pagos_pago_origen_id_foreign` (`pago_origen_id`),
  KEY `pagos_tipo_nota_credito_id_foreign` (`tipo_nota_credito_id`),
  KEY `pagos_tipo_nota_debito_id_foreign` (`tipo_nota_debito_id`),
  CONSTRAINT `pagos_caja_id_foreign` FOREIGN KEY (`caja_id`) REFERENCES `cajas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pagos_cliente_fiscal_id_foreign` FOREIGN KEY (`cliente_fiscal_id`) REFERENCES `clientes_fiscales` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pagos_consulta_id_foreign` FOREIGN KEY (`consulta_id`) REFERENCES `consultas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pagos_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pagos_pago_origen_id_foreign` FOREIGN KEY (`pago_origen_id`) REFERENCES `pagos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pagos_serie_id_foreign` FOREIGN KEY (`serie_id`) REFERENCES `series` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pagos_sucursal_id_foreign` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pagos_tipo_nota_credito_id_foreign` FOREIGN KEY (`tipo_nota_credito_id`) REFERENCES `tipos_nota_credito` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pagos_tipo_nota_debito_id_foreign` FOREIGN KEY (`tipo_nota_debito_id`) REFERENCES `tipos_nota_debito` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pagos_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pais`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pais` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_iso2` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_iso3` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_telefonico` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `moneda_principal` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `idioma_principal` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `continente` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitud` decimal(11,8) DEFAULT NULL,
  `longitud` decimal(11,8) DEFAULT NULL,
  `zona_horaria` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `formato_fecha` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'dd/mm/yyyy',
  `formato_moneda` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1.234,56',
  `impuesto_predeterminado` decimal(5,2) NOT NULL DEFAULT '0.00',
  `separador_miles` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '.',
  `separador_decimales` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ',',
  `decimales_moneda` int NOT NULL DEFAULT '2',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pais_codigo_iso2_unique` (`codigo_iso2`),
  UNIQUE KEY `pais_codigo_iso3_unique` (`codigo_iso3`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `module` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sector` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`),
  KEY `permissions_module_index` (`module`),
  KEY `permissions_sector_index` (`sector`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `preguntas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `preguntas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cuestionario_id` bigint unsigned NOT NULL,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `tipo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'texto',
  `opciones` json DEFAULT NULL,
  `obligatorio` tinyint(1) NOT NULL DEFAULT '1',
  `orden` int NOT NULL DEFAULT '0',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `preguntas_cuestionario_id_activo_index` (`cuestionario_id`,`activo`),
  KEY `preguntas_orden_index` (`orden`),
  CONSTRAINT `preguntas_cuestionario_id_foreign` FOREIGN KEY (`cuestionario_id`) REFERENCES `cuestionarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `reposos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reposos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `consulta_id` bigint unsigned NOT NULL,
  `paciente_id` bigint unsigned NOT NULL,
  `medico_id` bigint unsigned NOT NULL,
  `motivo` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `dias_reposo` int NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `empresa_id` bigint unsigned NOT NULL,
  `sucursal_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reposos_consulta_id_foreign` (`consulta_id`),
  KEY `reposos_paciente_id_foreign` (`paciente_id`),
  KEY `reposos_medico_id_foreign` (`medico_id`),
  KEY `reposos_empresa_id_foreign` (`empresa_id`),
  KEY `reposos_sucursal_id_foreign` (`sucursal_id`),
  CONSTRAINT `reposos_consulta_id_foreign` FOREIGN KEY (`consulta_id`) REFERENCES `consultas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reposos_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reposos_medico_id_foreign` FOREIGN KEY (`medico_id`) REFERENCES `medicos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reposos_paciente_id_foreign` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reposos_sucursal_id_foreign` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `respuesta_preconsultas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `respuesta_preconsultas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `paciente_id` bigint unsigned NOT NULL,
  `cita_id` bigint unsigned DEFAULT NULL,
  `consulta_id` bigint unsigned DEFAULT NULL,
  `pregunta_id` bigint unsigned NOT NULL,
  `respuesta` text COLLATE utf8mb4_unicode_ci,
  `respuesta_multiple` json DEFAULT NULL,
  `detalle` text COLLATE utf8mb4_unicode_ci,
  `token_unico` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `completado` tinyint(1) NOT NULL DEFAULT '0',
  `fecha_completado` timestamp NULL DEFAULT NULL,
  `empresa_id` bigint unsigned NOT NULL,
  `sucursal_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `respuesta_preconsultas_cita_id_foreign` (`cita_id`),
  KEY `respuesta_preconsultas_pregunta_id_foreign` (`pregunta_id`),
  KEY `respuesta_preconsultas_sucursal_id_foreign` (`sucursal_id`),
  KEY `respuesta_preconsultas_token_unico_completado_index` (`token_unico`,`completado`),
  KEY `respuesta_preconsultas_paciente_id_cita_id_index` (`paciente_id`,`cita_id`),
  KEY `respuesta_preconsultas_empresa_id_index` (`empresa_id`),
  KEY `respuesta_preconsultas_created_by_foreign` (`created_by`),
  KEY `respuesta_preconsultas_consulta_id_index` (`consulta_id`),
  CONSTRAINT `respuesta_preconsultas_cita_id_foreign` FOREIGN KEY (`cita_id`) REFERENCES `citas` (`id`),
  CONSTRAINT `respuesta_preconsultas_consulta_id_foreign` FOREIGN KEY (`consulta_id`) REFERENCES `consultas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `respuesta_preconsultas_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `pacientes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `respuesta_preconsultas_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `respuesta_preconsultas_paciente_id_foreign` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`),
  CONSTRAINT `respuesta_preconsultas_pregunta_id_foreign` FOREIGN KEY (`pregunta_id`) REFERENCES `preguntas` (`id`),
  CONSTRAINT `respuesta_preconsultas_sucursal_id_foreign` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `series`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `series` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tipo_documento` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `serie` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `correlativo_actual` int NOT NULL DEFAULT '0',
  `control_fiscal_actual` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `longitud_correlativo` int NOT NULL DEFAULT '8',
  `longitud_control_fiscal` int NOT NULL DEFAULT '8',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `empresa_id` bigint unsigned NOT NULL,
  `sucursal_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `series_serie_empresa_id_sucursal_id_unique` (`serie`,`empresa_id`,`sucursal_id`),
  KEY `series_empresa_id_foreign` (`empresa_id`),
  KEY `series_sucursal_id_foreign` (`sucursal_id`),
  KEY `series_tipo_documento_empresa_id_sucursal_id_activo_index` (`tipo_documento`,`empresa_id`,`sucursal_id`,`activo`),
  CONSTRAINT `series_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `series_sucursal_id_foreign` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `signos_vitales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `signos_vitales` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `consulta_id` bigint unsigned NOT NULL,
  `enfermero_id` bigint unsigned DEFAULT NULL,
  `paciente_id` bigint unsigned NOT NULL,
  `presion_arterial_sistolica` decimal(5,1) DEFAULT NULL,
  `presion_arterial_diastolica` decimal(5,1) DEFAULT NULL,
  `frecuencia_cardiaca` int DEFAULT NULL,
  `frecuencia_respiratoria` int DEFAULT NULL,
  `temperatura` decimal(4,1) DEFAULT NULL,
  `peso` decimal(6,2) DEFAULT NULL,
  `talla` decimal(5,1) DEFAULT NULL,
  `imc` decimal(4,1) DEFAULT NULL,
  `saturacion_oxigeno` decimal(5,1) DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `empresa_id` bigint unsigned NOT NULL,
  `sucursal_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `signos_vitales_paciente_id_foreign` (`paciente_id`),
  KEY `signos_vitales_sucursal_id_foreign` (`sucursal_id`),
  KEY `signos_vitales_created_by_foreign` (`created_by`),
  KEY `signos_vitales_updated_by_foreign` (`updated_by`),
  KEY `signos_vitales_consulta_id_enfermero_id_index` (`consulta_id`,`enfermero_id`),
  KEY `signos_vitales_empresa_id_sucursal_id_index` (`empresa_id`,`sucursal_id`),
  KEY `signos_vitales_enfermero_id_foreign` (`enfermero_id`),
  CONSTRAINT `signos_vitales_consulta_id_foreign` FOREIGN KEY (`consulta_id`) REFERENCES `consultas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `signos_vitales_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `signos_vitales_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `signos_vitales_enfermero_id_foreign` FOREIGN KEY (`enfermero_id`) REFERENCES `enfermeros` (`id`),
  CONSTRAINT `signos_vitales_paciente_id_foreign` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`),
  CONSTRAINT `signos_vitales_sucursal_id_foreign` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`),
  CONSTRAINT `signos_vitales_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `subespecialidades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subespecialidades` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `especialidad_id` bigint unsigned NOT NULL,
  `costo_consulta` decimal(10,2) DEFAULT NULL,
  `duracion_consulta` int NOT NULL DEFAULT '30',
  `requiere_cita_previa` tinyint(1) NOT NULL DEFAULT '1',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `empresa_id` bigint unsigned NOT NULL,
  `sucursal_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subespecialidades_codigo_unique` (`codigo`),
  KEY `subespecialidades_sucursal_id_foreign` (`sucursal_id`),
  KEY `subespecialidades_especialidad_id_status_index` (`especialidad_id`,`status`),
  KEY `subespecialidades_empresa_id_sucursal_id_index` (`empresa_id`,`sucursal_id`),
  KEY `subespecialidades_codigo_index` (`codigo`),
  CONSTRAINT `subespecialidades_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `subespecialidades_especialidad_id_foreign` FOREIGN KEY (`especialidad_id`) REFERENCES `especialidades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subespecialidades_sucursal_id_foreign` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sucursales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sucursales` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` text COLLATE utf8mb4_unicode_ci,
  `latitud` decimal(10,8) DEFAULT NULL,
  `longitud` decimal(11,8) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sucursales_empresa_id_foreign` (`empresa_id`),
  CONSTRAINT `sucursales_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `template_customizations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `template_customizations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `primary_color` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#7367F0',
  `skin` int NOT NULL DEFAULT '0',
  `theme` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'light',
  `semi_dark` tinyint(1) NOT NULL DEFAULT '0',
  `content_layout` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'compact',
  `header_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'static',
  `menu_collapsed` tinyint(1) NOT NULL DEFAULT '0',
  `navbar_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sticky',
  `text_direction` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ltr',
  `footer_fixed` tinyint(1) NOT NULL DEFAULT '0',
  `dropdown_on_hover` tinyint(1) NOT NULL DEFAULT '0',
  `layout_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'vertical',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tipo_consultas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_consultas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` int NOT NULL DEFAULT '0',
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#4e73df',
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icono` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fas fa-notes-medical',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `empresa_id` bigint unsigned NOT NULL,
  `sucursal_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tipo_consultas_sucursal_id_foreign` (`sucursal_id`),
  KEY `tipo_consultas_empresa_id_status_index` (`empresa_id`,`status`),
  CONSTRAINT `tipo_consultas_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tipo_consultas_sucursal_id_foreign` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tipos_nota_credito`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipos_nota_credito` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tipos_nota_credito_codigo_unique` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tipos_nota_debito`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipos_nota_debito` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tipos_nota_debito_codigo_unique` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tutores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tutores` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `paciente_id` bigint unsigned NOT NULL,
  `nombres` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `documento_identidad` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parentesco` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `edad` int DEFAULT NULL,
  `telefono` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tutores_paciente_id_index` (`paciente_id`),
  CONSTRAINT `tutores_paciente_id_foreign` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp_verification_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `verification_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `two_factor_secret` text COLLATE utf8mb4_unicode_ci,
  `two_factor_recovery_codes` json DEFAULT NULL,
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `verification_code_sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `sucursal_id` bigint unsigned DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preferred_devices` json DEFAULT NULL,
  `common_locations` json DEFAULT NULL,
  `total_session_time` int NOT NULL DEFAULT '0',
  `security_alerts` json DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `failed_login_attempts` int unsigned NOT NULL DEFAULT '0',
  `locked_until` timestamp NULL DEFAULT NULL,
  `last_failed_login_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`),
  KEY `users_empresa_id_foreign` (`empresa_id`),
  KEY `users_sucursal_id_foreign` (`sucursal_id`),
  CONSTRAINT `users_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_sucursal_id_foreign` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `whatsapp_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `whatsapp_messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `message_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `template_id` bigint unsigned DEFAULT NULL,
  `recipient_phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message_content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `variables` json DEFAULT NULL,
  `status` enum('pending','sent','delivered','read','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `sent_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `direction` enum('inbound','outbound') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'outbound',
  `created_by` bigint unsigned DEFAULT NULL,
  `cost` decimal(8,4) DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `retry_count` tinyint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `whatsapp_messages_message_id_unique` (`message_id`),
  KEY `whatsapp_messages_template_id_foreign` (`template_id`),
  KEY `whatsapp_messages_created_by_foreign` (`created_by`),
  KEY `whatsapp_messages_message_id_index` (`message_id`),
  KEY `whatsapp_messages_recipient_phone_index` (`recipient_phone`),
  KEY `whatsapp_messages_status_index` (`status`),
  KEY `whatsapp_messages_direction_index` (`direction`),
  KEY `whatsapp_messages_sent_at_index` (`sent_at`),
  KEY `whatsapp_messages_status_created_at_index` (`status`,`created_at`),
  KEY `whatsapp_messages_recipient_phone_created_at_index` (`recipient_phone`,`created_at`),
  KEY `idx_direction_status_retry` (`direction`,`status`,`retry_count`),
  KEY `idx_created_direction_retry` (`created_at`,`direction`,`retry_count`),
  CONSTRAINT `whatsapp_messages_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `whatsapp_messages_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `whatsapp_templates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `whatsapp_scheduled_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `whatsapp_scheduled_messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cita_id` bigint unsigned DEFAULT NULL,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `notification_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `template_id` bigint unsigned DEFAULT NULL,
  `recipient_phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message_content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `variables` json DEFAULT NULL,
  `scheduled_at` timestamp NOT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending','sent','failed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `attempts` int unsigned NOT NULL DEFAULT '0',
  `max_attempts` int unsigned NOT NULL DEFAULT '3',
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wsm_cita_type_phone_unique` (`cita_id`,`notification_type`,`recipient_phone`),
  KEY `whatsapp_scheduled_messages_template_id_foreign` (`template_id`),
  KEY `whatsapp_scheduled_messages_created_by_foreign` (`created_by`),
  KEY `whatsapp_scheduled_messages_status_index` (`status`),
  KEY `whatsapp_scheduled_messages_scheduled_at_index` (`scheduled_at`),
  KEY `whatsapp_scheduled_messages_recipient_phone_index` (`recipient_phone`),
  KEY `whatsapp_scheduled_messages_status_scheduled_at_index` (`status`,`scheduled_at`),
  KEY `whatsapp_scheduled_messages_empresa_id_foreign` (`empresa_id`),
  CONSTRAINT `whatsapp_scheduled_messages_cita_id_foreign` FOREIGN KEY (`cita_id`) REFERENCES `citas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `whatsapp_scheduled_messages_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `whatsapp_scheduled_messages_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `whatsapp_scheduled_messages_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `whatsapp_templates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `whatsapp_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `whatsapp_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `variables` json DEFAULT NULL,
  `category` enum('notification','reminder','marketing','transactional','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint unsigned DEFAULT NULL,
  `usage_count` int unsigned NOT NULL DEFAULT '0',
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `whatsapp_templates_name_unique` (`name`),
  KEY `whatsapp_templates_created_by_foreign` (`created_by`),
  KEY `whatsapp_templates_category_index` (`category`),
  KEY `whatsapp_templates_is_active_index` (`is_active`),
  KEY `whatsapp_templates_usage_count_index` (`usage_count`),
  CONSTRAINT `whatsapp_templates_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_01_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2023_10_21_000000_add_two_factor_columns_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2024_01_01_000000_create_template_customizations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2024_12_15_000001_create_whatsapp_templates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2024_12_15_000002_create_whatsapp_scheduled_messages_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2024_12_15_000003_create_whatsapp_messages_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2024_12_19_000000_add_retry_count_to_whatsapp_messages_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2025_10_17_000000_add_verification_code_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2025_10_17_000001_create_active_sessions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2025_10_17_000002_add_fields_to_active_sessions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2025_10_17_205657_create_empresas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2025_10_17_205659_add_api_key_to_empresas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2025_10_17_205659_update_empresas_api_key_column',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2025_10_17_214709_create_sucursales_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2025_10_20_132700_add_empresa_id_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2025_10_20_171800_add_profile_fields_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2025_10_20_200325_create_permission_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2025_10_22_201649_add_telefono_and_email_to_empresas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2025_10_24_162929_add_module_to_permissions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2025_10_27_020859_create_notifications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2025_10_27_022318_create_activity_log_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2025_10_27_022319_add_event_column_to_activity_log_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2025_10_27_022320_add_batch_uuid_column_to_activity_log_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2025_10_27_210941_create_conceptos_pago_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2025_10_28_144539_add_empresa_sucursal_to_conceptos_pago_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2025_11_01_000000_create_series_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2025_11_03_000001_create_cajas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2025_11_03_160220_create_exchange_rates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2025_11_03_161309_fix_exchange_rates_source_column',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2025_11_03_162823_add_numero_corte_to_cajas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2025_11_05_100000_remove_unique_constraint_from_cajas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2025_11_06_090055_create_pais_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2025_11_06_090208_add_pais_id_to_empresas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2025_11_07_000000_create_late_payment_rules_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2025_11_29_000000_add_username_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2025_12_04_000000_add_whatsapp_api_key_to_empresas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2026_01_21_134944_add_latitud_longitud_to_pais_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2026_02_04_175733_add_whatsapp_fields_to_empresas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2026_02_04_190000_increase_whatsapp_phone_field_size',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2026_02_05_140000_create_especialidades_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2026_02_05_160000_create_subespecialidades_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2026_02_05_170000_add_color_to_subespecialidades_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2026_02_05_180000_create_medicos_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2026_02_05_190000_add_experiencia_nivel_to_medico_subespecialidad_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2026_02_05_200000_create_medico_horarios_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2026_02_06_000000_create_pacientes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2026_02_06_100000_create_citas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2026_02_06_100001_create_cita_recordatorios_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2026_02_06_100002_add_intentos_to_cita_recordatorios_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2026_02_06_120000_add_especialidad_to_citas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (53,'2026_02_06_155446_add_phone_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (54,'2026_02_06_200000_add_cita_fields_to_whatsapp_scheduled_messages_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (55,'2026_02_06_200000_create_tipo_consultas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (56,'2026_02_06_200001_add_tipo_consulta_id_to_citas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (57,'2026_02_06_244551_add_codigo_to_tipo_consultas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (58,'2026_02_06_300000_add_soft_deletes_and_cancellation_to_citas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (59,'2026_02_06_300001_add_fields_to_pacientes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (60,'2026_02_07_000001_add_sector_to_permissions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (61,'2026_02_07_200000_add_email_and_direccion_to_tutores_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (62,'2026_02_09_145002_add_confirmation_fields_to_citas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (63,'2026_02_09_194206_create_cita_confirmaciones_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (64,'2026_02_11_120000_add_genero_to_medicos_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (65,'2026_02_12_071659_create_consultorios_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (66,'2026_02_12_120000_create_exchange_rate_monthly_histories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (67,'2026_02_12_121000_create_exchange_rate_daily_histories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (68,'2026_02_12_122000_alter_exchange_rate_monthly_histories_add_daily_records',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (69,'2026_02_12_141903_create_cuestionarios_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (70,'2026_02_12_141918_create_preguntas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (71,'2026_02_12_141930_create_respuesta_preconsultas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (72,'2026_02_12_142044_add_preconsulta_fields_to_citas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (73,'2026_02_12_190600_create_consultas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (74,'2026_02_17_000001_add_motivo_consulta_to_consultas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (75,'2026_02_17_000001_create_enfermeros_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (76,'2026_02_17_000002_create_enfermero_horarios_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (77,'2026_02_17_000003_create_enfermero_especialidades_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (78,'2026_02_17_153234_add_detalle_to_respuestas_preconsulta_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (79,'2026_02_17_154044_add_created_by_to_respuesta_preconsultas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (80,'2026_02_17_173827_add_estado_changed_at_to_consultas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (81,'2026_02_17_195011_create_signos_vitales_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (82,'2026_02_20_134551_create_consulta_estudios_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (83,'2026_02_20_134551_create_consulta_evaluaciones_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (84,'2026_02_20_134552_create_consulta_tratamientos_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (85,'2026_02_20_140939_add_consulta_id_to_respuesta_preconsultas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (86,'2026_02_20_141841_create_diagnosticos_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (87,'2026_02_20_141842_create_consulta_diagnostico_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (88,'2026_02_20_142309_add_sucursal_id_to_diagnosticos_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (89,'2026_02_20_142737_make_enfermero_id_nullable_in_signos_vitales_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (90,'2026_02_20_161534_create_reposos_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (91,'2026_02_21_083844_create_clientes_fiscales_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (92,'2026_02_21_083901_create_impuestos_configuracion_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (93,'2026_02_21_084007_add_fiscal_fields_to_empresas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (94,'2026_02_21_084202_create_pagos_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (95,'2026_02_21_084203_add_fiscal_fields_to_pagos_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (96,'2026_02_21_085502_create_baremos_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (97,'2026_02_21_184048_create_tipos_nota_credito_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (98,'2026_02_21_184048_create_tipos_nota_debito_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (99,'2026_02_21_184049_add_nota_fields_to_pagos_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (100,'2026_02_21_185524_add_sucursal_id_to_clientes_fiscales_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (101,'2026_02_21_200000_improve_fiscal_seniat_compliance',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (102,'2026_02_22_000001_add_sucursal_id_to_impuestos_configuracion_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (103,'2026_02_22_000001_create_cuentas_contables_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (104,'2026_02_22_000002_add_fiscal_fields_to_pagos_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (105,'2026_02_22_000002_create_asientos_contables_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (106,'2026_02_22_000003_create_asientos_detalles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (107,'2026_02_22_081219_add_numero_control_fiscal_to_series_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (108,'2026_02_22_0845504_create_pago_detalles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (109,'2026_02_23_100000_create_anulacion_talonarios_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (110,'2026_02_24_100000_create_audit_logs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (111,'2026_02_24_110000_add_login_security_fields_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (112,'2026_03_02_000001_create_chat_messages_table',1);
