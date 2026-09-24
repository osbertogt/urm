// curvas_oms.js
class CurvasCrecimientoOMS {
    constructor() {
        this.chartTalla = null;
        this.chartPeso = null;
        this.chartCirc = null;
        this.datosPaciente = null;
        this.sexoPaciente = 'F';
        this.init();
    }

    init() {
        // Cargar datos cuando se muestra el tab
        $('#tabCurvasOMS-tab').on('shown.bs.tab', () => {
            this.cargarDatosPaciente();
        });
    }

    cargarDatosPaciente() {
        const idCliente = $("#numerocliente").val();
        
        if (!idCliente || idCliente == 0) {
            this.mostrarError("No hay paciente seleccionado");
            return;
        }

        // Mostrar loading
        $('#loadingTable').show();
        $('.card-body tbody').empty();

        $.ajax({
            url: FORM_URL + 'cons/pdt/curvas_oms_ajax.php',
            type: 'POST',
            data: { id_cliente: idCliente },
            dataType: 'json',
            success: (response) => {
                if (response.success) {
                    this.datosPaciente = response.datos;
                    this.sexoPaciente = response.paciente.sexo;
                    
                    // Actualizar tabla
                    this.actualizarTabla(response);
                    
                    // Generar gráficas
                    this.generarGraficas();
                    
                    // Ocultar loading
                    $('#loadingTable').hide();
                } else {
                    this.mostrarError(response.message || "Error al cargar datos");
                }
            },
            error: (xhr, status, error) => {
                this.mostrarError("Error de conexión: " + error);
                $('#loadingTable').hide();
            }
        });
    }

    actualizarTabla(response) {
        const tbody = $('#tblDatosAntro tbody');
        tbody.empty();

        if (!this.datosPaciente || this.datosPaciente.length === 0) {
            tbody.html(`
                <tr>
                    <td colspan="7" class="text-center text-muted">
                        <i class="bi bi-exclamation-triangle"></i> No hay datos antropométricos registrados
                    </td>
                </tr>
            `);
            return;
        }

        this.datosPaciente.forEach((registro, index) => {
            // Calcular percentiles para esta edad
            const percentilesTalla = this.obtenerPercentiles('talla', registro.edad_meses);
            const zScore = percentilesTalla ? 
                this.calcularZScore(registro.talla, percentilesTalla) : null;
            const percentilTalla = percentilesTalla ? 
                this.determinarPercentil(registro.talla, percentilesTalla) : 'N/A';

            const row = `
                <tr>
                    <td>${registro.fecha}</td>
                    <td class="text-center">${registro.edad_meses.toFixed(1)}</td>
                    <td class="text-center">${registro.talla ? registro.talla.toFixed(1) : '-'}</td>
                    <td class="text-center">${registro.peso ? registro.peso.toFixed(2) : '-'}</td>
                    <td class="text-center">${registro.circ_cefalica ? registro.circ_cefalica.toFixed(1) : '-'}</td>
                    <td class="text-center">
                        <span class="badge ${this.getPercentilBadgeClass(percentilTalla)}">
                            ${percentilTalla}
                        </span>
                    </td>
                    <td class="text-center">
                        ${zScore ? zScore.toFixed(2) : '-'}
                    </td>
                </tr>
            `;
            tbody.append(row);
        });

        // Actualizar información del paciente
        this.actualizarInfoPaciente(response.paciente);
    }

    actualizarInfoPaciente(paciente) {
        const infoHtml = `
            <div class="alert alert-light">
                <h6><i class="bi bi-person-circle"></i> Información del Paciente</h6>
                <p class="mb-1"><strong>Nombre:</strong> ${paciente.nombre}</p>
                <p class="mb-1"><strong>Sexo:</strong> ${paciente.sexo === 'F' ? 'Femenino' : 'Masculino'}</p>
                <p class="mb-1"><strong>Fecha Nacimiento:</strong> ${paciente.fecha_nacimiento}</p>
                <p class="mb-0"><strong>Registros:</strong> ${this.datosPaciente.length}</p>
            </div>
        `;
        
        // Actualizar en cada card
        $('#infoTalla').html(infoHtml);
        $('#infoPeso').html(infoHtml);
        $('#infoCirc').html(infoHtml);
    }

    obtenerPercentiles(tipo, edadMeses) {
        // En una implementación real, esto llamaría a tu API PHP
        // Por ahora simulamos con datos estáticos
        return this.simularPercentilesOMS(tipo, this.sexoPaciente, edadMeses);
    }

    simularPercentilesOMS(tipo, sexo, edadMeses) {
        // Datos de ejemplo - en producción usarías las tablas OMS reales
        const baseValues = {
            'talla': { P3: 45, P15: 47, P50: 49, P85: 51, P97: 53 },
            'peso': { P3: 2.5, P15: 3.0, P50: 3.5, P85: 4.0, P97: 4.5 },
            'circ_cefalica': { P3: 32, P15: 33, P50: 34, P85: 35, P97: 36 }
        };

        const base = baseValues[tipo];
        const crecimiento = edadMeses * 0.5; // Simulación de crecimiento
        
        return {
            P3: base.P3 + crecimiento,
            P15: base.P15 + crecimiento,
            P50: base.P50 + crecimiento,
            P85: base.P85 + crecimiento,
            P97: base.P97 + crecimiento
        };
    }

    calcularZScore(valor, percentiles) {
        if (!valor || !percentiles) return null;
        
        const p50 = percentiles.P50;
        const sd = (percentiles.P85 - p50) / 1.036; // Aproximación SD
        
        if (sd > 0) {
            return (valor - p50) / sd;
        }
        return null;
    }

    determinarPercentil(valor, percentiles) {
        if (!valor || !percentiles) return 'N/A';
        
        if (valor <= percentiles.P3) return '< P3';
        if (valor <= percentiles.P15) return 'P3-P15';
        if (valor <= percentiles.P50) return 'P15-P50';
        if (valor <= percentiles.P85) return 'P50-P85';
        if (valor <= percentiles.P97) return 'P85-P97';
        return '> P97';
    }

    getPercentilBadgeClass(percentil) {
        switch(percentil) {
            case '< P3':
            case '> P97':
                return 'bg-danger';
            case 'P3-P15':
            case 'P85-P97':
                return 'bg-warning';
            case 'P15-P50':
            case 'P50-P85':
                return 'bg-success';
            default:
                return 'bg-secondary';
        }
    }

    generarGraficas() {
        if (!this.datosPaciente || this.datosPaciente.length === 0) {
            this.mostrarMensajeSinDatos();
            return;
        }

        // Destruir gráficas existentes
        if (this.chartTalla) this.chartTalla.destroy();
        if (this.chartPeso) this.chartPeso.destroy();
        if (this.chartCirc) this.chartCirc.destroy();

        // Preparar datos
        const datosGrafica = this.prepararDatosGrafica();

        // Crear gráficas
        this.chartTalla = this.crearGraficaTalla(datosGrafica);
        this.chartPeso = this.crearGraficaPeso(datosGrafica);
        this.chartCirc = this.crearGraficaCirc(datosGrafica);
    }

    prepararDatosGrafica() {
        const datos = {
            edades: [],
            tallas: [],
            pesos: [],
            circs: [],
            fechas: []
        };

        // Filtrar y ordenar datos
        const datosFiltrados = this.datosPaciente
            .filter(r => r.edad_meses <= 60) // Solo hasta 5 años
            .sort((a, b) => a.edad_meses - b.edad_meses);

        datosFiltrados.forEach(registro => {
            datos.edades.push(registro.edad_meses);
            datos.tallas.push(registro.talla);
            datos.pesos.push(registro.peso);
            datos.circs.push(registro.circ_cefalica);
            datos.fechas.push(registro.fecha);
        });

        return datos;
    }

    crearGraficaTalla(datos) {
        const ctx = document.getElementById('chartTallaOMS').getContext('2d');
        
        // Generar curvas de percentiles
        const percentilesData = this.generarCurvasPercentiles('talla', datos.edades);
        
        return new Chart(ctx, {
            type: 'scatter',
            data: {
                datasets: [
                    // Percentil P3
                    {
                        label: 'P3',
                        data: percentilesData.P3,
                        borderColor: 'rgba(220, 53, 69, 0.5)',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        borderWidth: 1,
                        pointRadius: 0,
                        fill: false,
                        tension: 0.4
                    },
                    // Percentil P15
                    {
                        label: 'P15',
                        data: percentilesData.P15,
                        borderColor: 'rgba(255, 193, 7, 0.5)',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        borderWidth: 1,
                        pointRadius: 0,
                        fill: false,
                        tension: 0.4
                    },
                    // Percentil P50
                    {
                        label: 'P50',
                        data: percentilesData.P50,
                        borderColor: 'rgba(40, 167, 69, 0.7)',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        borderWidth: 2,
                        pointRadius: 0,
                        fill: false,
                        tension: 0.4
                    },
                    // Percentil P85
                    {
                        label: 'P85',
                        data: percentilesData.P85,
                        borderColor: 'rgba(255, 193, 7, 0.5)',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        borderWidth: 1,
                        pointRadius: 0,
                        fill: false,
                        tension: 0.4
                    },
                    // Percentil P97
                    {
                        label: 'P97',
                        data: percentilesData.P97,
                        borderColor: 'rgba(220, 53, 69, 0.5)',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        borderWidth: 1,
                        pointRadius: 0,
                        fill: false,
                        tension: 0.4
                    },
                    // Datos del paciente
                    {
                        label: 'Paciente',
                        data: datos.edades.map((edad, i) => ({
                            x: edad,
                            y: datos.tallas[i]
                        })).filter(p => p.y !== null),
                        borderColor: 'rgba(13, 110, 253, 1)',
                        backgroundColor: 'rgba(13, 110, 253, 0.8)',
                        borderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        showLine: true,
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Talla para la Edad - Percentiles OMS',
                        font: { size: 16 }
                    },
                    tooltip: {
                        callbacks: {
                            label: (context) => {
                                const label = context.dataset.label || '';
                                if (label === 'Paciente') {
                                    const index = context.dataIndex;
                                    return `${label}: ${context.parsed.y.toFixed(1)} cm (${datos.fechas[index]})`;
                                }
                                return `${label}: ${context.parsed.y.toFixed(1)} cm`;
                            }
                        }
                    },
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Edad (meses)'
                        },
                        min: 0,
                        max: Math.max(...datos.edades) + 2
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Talla (cm)'
                        },
                        beginAtZero: false
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    }

    crearGraficaPeso(datos) {
        const ctx = document.getElementById('chartPesoOMS').getContext('2d');
        
        // Similar a crearGraficaTalla pero con datos de peso
        const percentilesData = this.generarCurvasPercentiles('peso', datos.edades);
        
        return new Chart(ctx, {
            type: 'scatter',
            data: {
                datasets: [
                    // ... config similar a talla pero para peso
                ]
            },
            options: {
                // ... opciones similares
            }
        });
    }

    crearGraficaCirc(datos) {
        const ctx = document.getElementById('chartCircOMS').getContext('2d');
        
        // Similar a crearGraficaTalla pero con datos de circunferencia
        const percentilesData = this.generarCurvasPercentiles('circ_cefalica', datos.edades);
        
        return new Chart(ctx, {
            type: 'scatter',
            data: {
                datasets: [
                    // ... config similar a talla pero para circunferencia
                ]
            },
            options: {
                // ... opciones similares
            }
        });
    }

    generarCurvasPercentiles(tipo, edades) {
        const result = {
            P3: [],
            P15: [],
            P50: [],
            P85: [],
            P97: []
        };

        // Generar puntos para cada percentil en cada edad
        edades.forEach(edad => {
            const percentiles = this.obtenerPercentiles(tipo, edad);
            
            if (percentiles) {
                result.P3.push({ x: edad, y: percentiles.P3 });
                result.P15.push({ x: edad, y: percentiles.P15 });
                result.P50.push({ x: edad, y: percentiles.P50 });
                result.P85.push({ x: edad, y: percentiles.P85 });
                result.P97.push({ x: edad, y: percentiles.P97 });
            }
        });

        return result;
    }

    mostrarError(mensaje) {
        const errorHtml = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> ${mensaje}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $('.card-body').prepend(errorHtml);
    }

    mostrarMensajeSinDatos() {
        const mensaje = `
            <div class="alert alert-warning">
                <i class="bi bi-info-circle"></i> No hay suficientes datos antropométricos para generar gráficas.
                <br><small>Se requieren al menos 2 registros con datos válidos.</small>
            </div>
        `;
        
        $('#chartTallaOMS').closest('.card-body').html(mensaje);
        $('#chartPesoOMS').closest('.card-body').html(mensaje);
        $('#chartCircOMS').closest('.card-body').html(mensaje);
    }
}

// Inicializar cuando el DOM esté listo
$(document).ready(function() {
    window.curvasOMS = new CurvasCrecimientoOMS();
});