@extends('layouts.app')
@section('title', 'Аналитика')

@section('content')
<div class="admin-analytics">
    <div class="admin-analytics__header">
        <h1 class="admin-analytics__title">Аналитика</h1>
    </div>
    
    <div class="admin-analytics__content">
        <div class="admin-analytics__stats-cards">
            <div class="stats-card">
                <div class="stats-card__title">Всего сотрудников</div>
                <div class="stats-card__value">{{ $totalUsers }}</div>
            </div>
            <div class="stats-card">
                <div class="stats-card__title">Всего отделов</div>
                <div class="stats-card__value">{{ $totalDepartments }}</div>
            </div>
            <div class="stats-card">
                <div class="stats-card__title">Всего должностей</div>
                <div class="stats-card__value">{{ $totalPositions }}</div>
            </div>
        </div>
        
        <div class="admin-analytics__charts">
            
            <div class="chart-container">
                <h3>Сотрудники по отделам</h3>
                <canvas id="usersByDepartmentChart"></canvas>
            </div>
            <div class="chart-container">
                <h3>Статистика посещаемости за текущий месяц</h3>
                <canvas id="attendanceStatsLastMonthChart"></canvas>
            </div>
            <div class="chart-container">
                <h3>Сотрудники по должностям</h3>
                <canvas id="usersByPositionChart"></canvas>
            </div>
            <div class="chart-container admin-analytics__trend-chart">
                <h3>Динамика заявок за последний год</h3>
                <canvas id="leaveRequestsTrendChart"></canvas>
            </div>
            
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-moment@1.0.1"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Данные из контроллера
    const leaveRequestsByType = @json($leaveRequestsByType);
    const leaveRequestStatuses = @json($leaveRequestStatuses);
    const usersByDepartment = @json($usersByDepartment);
    const usersByPosition = @json($usersByPosition);
    const leaveRequestsTrend = @json($leaveRequestsTrend);
    const attendanceStatsLastMonth = @json($attendanceStatsLastMonth);

    // Добавляем отладочную информацию
    console.log('Attendance Stats:', attendanceStatsLastMonth);

    // Функция для получения названия месяца на русском
    function getMonthName(monthNumber) {
        const date = new Date();
        date.setMonth(monthNumber - 1);
        return date.toLocaleString('ru-RU', { month: 'long' });
    }

    // Преобразование данных для графика динамики
    const trendData = leaveRequestsTrend.reduce((acc, item) => {
        const monthYear = `${getMonthName(item.month)} ${item.year}`;
        if (!acc[monthYear]) {
            acc[monthYear] = {};
        }
        acc[monthYear][item.type] = item.total;
        return acc;
    }, {});

    const labels = Object.keys(trendData);
    const vacationData = labels.map(label => trendData[label].vacation || 0);
    const sickLeaveData = labels.map(label => trendData[label].sick_leave || 0);
    const businessTripData = labels.map(label => trendData[label].business_trip || 0);

    
    // График статистики посещаемости за текущий месяц
    const attendanceStatsLastMonthCtx = document.getElementById('attendanceStatsLastMonthChart').getContext('2d');
    new Chart(attendanceStatsLastMonthCtx, {
        type: 'bar',
        data: {
            labels: ['Явка', 'Неявка', 'Отпуск', 'Больничный'],
            datasets: [{
                label: 'Количество',
                data: [
                    attendanceStatsLastMonth.present || 0,
                    attendanceStatsLastMonth.absent || 0,
                    attendanceStatsLastMonth.vacation || 0,
                    attendanceStatsLastMonth.sick_leave || 0
                ],
                backgroundColor: [
                    '#4CAF50', // Зеленый (Явка)
                    '#F44336', // Красный (Неявка)
                    '#2196F3', // Синий (Отпуск)
                    '#FF9800'  // Оранжевый (Больничный)
                ],
                borderColor: '#ffffff',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false,
                },
                title: {
                    display: false,
                    text: 'Статистика посещаемости за текущий месяц'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        },
    });

    // График сотрудников по отделам
    const usersByDepartmentCtx = document.getElementById('usersByDepartmentChart').getContext('2d');
    new Chart(usersByDepartmentCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(usersByDepartment),
            datasets: [{
                label: 'Количество',
                data: Object.values(usersByDepartment),
                backgroundColor: [
                    '#3F51B5', // Индиго
                    '#00BCD4', // Голубой
                    '#FFEB3B', // Желтый
                    '#8BC34A', // Светло-зеленый
                    '#FF5722', // Глубокий оранжевый
                    '#E91E63', // Розовый
                    '#673AB7', // Фиолетовый
                    '#009688', // Бирюзовый
                    '#CDDC39', // Лаймовый
                    '#FFC107'  // Янтарный
                ],
                borderColor: '#ffffff',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: false,
                    text: 'Сотрудники по отделам'
                }
            }
        },
    });

    // График сотрудников по должностям
    const usersByPositionCtx = document.getElementById('usersByPositionChart').getContext('2d');
    new Chart(usersByPositionCtx, {
        type: 'bar',
        data: {
            labels: Object.keys(usersByPosition),
            datasets: [{
                label: 'Количество',
                data: Object.values(usersByPosition),
                backgroundColor: '#4CAF50', // Зеленый
                borderColor: '#ffffff',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false,
                },
                title: {
                    display: false,
                    text: 'Сотрудники по должностям'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        },
    });

    // График динамики заявок по месяцам
    const leaveRequestsTrendCtx = document.getElementById('leaveRequestsTrendChart').getContext('2d');
    new Chart(leaveRequestsTrendCtx, {
        type: 'line',
        data: {
            labels: labels, // Месяца и годы
            datasets: [
                {
                    label: 'Отпуск',
                    data: vacationData,
                    borderColor: '#2196F3',
                    backgroundColor: 'rgba(33, 150, 243, 0.2)',
                    fill: true,
                    tension: 0.1
                },
                {
                    label: 'Больничный',
                    data: sickLeaveData,
                    borderColor: '#FF9800',
                    backgroundColor: 'rgba(255, 152, 0, 0.2)',
                    fill: true,
                    tension: 0.1
                },
                {
                    label: 'Командировка',
                    data: businessTripData,
                    borderColor: '#9C27B0',
                    backgroundColor: 'rgba(156, 39, 176, 0.2)',
                    fill: true,
                    tension: 0.1
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: false,
                    text: 'Динамика заявок за последний год'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                         stepSize: 1
                    }
                },
                x: {
                     // Временная шкала, если используем даты в качестве меток
                     // type: 'time',
                     // time: {
                     //     unit: 'month',
                     //     tooltipFormat: 'MM.YYYY',
                     //     displayFormats: {
                     //         month: 'MMM YYYY'
                     //     }
                     // }
                }
            }
        },
    });
});
</script>
@endpush

@push('styles')
<style>
.admin-analytics__stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stats-card {
    background-color: #fff;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    text-align: center;
    border: 1px solid #eee;
}

.stats-card__title {
    font-size: 1rem;
    color: #666;
    margin-bottom: 10px;
}

.stats-card__value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--color-primary);
}

.admin-analytics__charts {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); /* Адаптивность на маленьких экранах */
    gap: 30px;
}

.chart-container {
    background-color: #fff;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    border: 1px solid #eee;
}

.chart-container h3 {
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--color-black);
    margin-top: 0;
    margin-bottom: 15px;
    text-align: center;
}

@media (min-width: 768px) { 
    .admin-analytics__charts {
        grid-template-columns: repeat(2, 1fr);
    }
    .admin-analytics__charts .chart-container {
        grid-column: auto;
        grid-row: auto;
    }
}
</style>
@endpush 