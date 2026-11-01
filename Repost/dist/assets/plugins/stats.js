document.addEventListener("DOMContentLoaded", () => {
  const dataElement = document.getElementById("stats-data");

  if (!dataElement || typeof ApexCharts === "undefined") return;

  const rootStyles = getComputedStyle(document.documentElement);
  const getColor = (name, fallback) =>
    rootStyles.getPropertyValue(name).trim() || fallback;
  const textColor = getColor("--text-color", "#212529");
  const mutedColor = getColor("--muted", "#64748b");
  const borderColor = getColor("--border-color", "#eef0f5");
  const primaryColor = getColor("--primary", "#0d6efd");
  const greenColor = getColor("--green", "#17b548");
  const redColor = getColor("--red", "#fb490a");
  const orangeColor = "#ff9f1c";
  const violetColor = "#7c3aed";

  const toNumber = (value) => {
    const normalized = String(value ?? "")
      .replace(/[^\d.,-]/g, "")
      .replace(",", ".");
    const number = Number(normalized);

    return Number.isFinite(number) ? number : 0;
  };

  const stats = {
    newsNum: toNumber(dataElement.dataset.newsNum),
    newsAllow: toNumber(dataElement.dataset.newsAllow),
    newsMain: toNumber(dataElement.dataset.newsMain),
    newsModer: toNumber(dataElement.dataset.newsModer),
    newsDay: toNumber(dataElement.dataset.newsDay),
    newsWeek: toNumber(dataElement.dataset.newsWeek),
    newsMonth: toNumber(dataElement.dataset.newsMonth),
    commNum: toNumber(dataElement.dataset.commNum),
    commDay: toNumber(dataElement.dataset.commDay),
    commWeek: toNumber(dataElement.dataset.commWeek),
    commMonth: toNumber(dataElement.dataset.commMonth),
    userNum: toNumber(dataElement.dataset.userNum),
    userBanned: toNumber(dataElement.dataset.userBanned),
    userDay: toNumber(dataElement.dataset.userDay),
    userWeek: toNumber(dataElement.dataset.userWeek),
    userMonth: toNumber(dataElement.dataset.userMonth),
    usersOnline: toNumber(dataElement.dataset.usersOnline),
  };

  const baseChartOptions = {
    chart: {
      fontFamily: "Inter, system-ui, sans-serif",
      toolbar: {
        show: false,
      },
      zoom: {
        enabled: false,
      },
    },
    colors: [primaryColor, greenColor, orangeColor, violetColor],
    dataLabels: {
      enabled: false,
    },
    grid: {
      borderColor,
      strokeDashArray: 4,
      padding: {
        left: 8,
        right: 12,
      },
    },
    legend: {
      labels: {
        colors: textColor,
      },
      markers: {
        radius: 12,
      },
    },
    stroke: {
      curve: "smooth",
      width: 3,
    },
    tooltip: {
      theme:
        document.documentElement.dataset.theme === "dark" ? "dark" : "light",
    },
    xaxis: {
      labels: {
        style: {
          colors: mutedColor,
        },
      },
      axisBorder: {
        color: borderColor,
      },
      axisTicks: {
        color: borderColor,
      },
    },
    yaxis: {
      labels: {
        style: {
          colors: mutedColor,
        },
      },
    },
  };

  const renderChart = (selector, options) => {
    const element = document.querySelector(selector);

    if (!element) return;

    new ApexCharts(element, options).render();
  };

  renderChart("#statsTimelineChart", {
    ...baseChartOptions,
    chart: {
      ...baseChartOptions.chart,
      type: "area",
      height: 360,
    },
    fill: {
      type: "gradient",
      gradient: {
        shadeIntensity: 1,
        opacityFrom: 0.45,
        opacityTo: 0.05,
        stops: [0, 90, 100],
      },
    },
    series: [
      {
        name: "Publications",
        data: [stats.newsDay, stats.newsWeek, stats.newsMonth],
      },
      {
        name: "Users",
        data: [stats.userDay, stats.userWeek, stats.userMonth],
      },
      {
        name: "Comments",
        data: [stats.commDay, stats.commWeek, stats.commMonth],
      },
    ],
    xaxis: {
      ...baseChartOptions.xaxis,
      categories: ["Day", "Week", "Month"],
    },
  });

  renderChart("#statsPostsChart", {
    ...baseChartOptions,
    chart: {
      ...baseChartOptions.chart,
      type: "area",
      height: 320,
    },
    colors: [primaryColor],
    fill: {
      type: "gradient",
      gradient: {
        opacityFrom: 0.5,
        opacityTo: 0.08,
      },
    },
    series: [
      {
        name: "Publications",
        data: [stats.newsNum, stats.newsAllow, stats.newsMain, stats.newsModer],
      },
    ],
    xaxis: {
      ...baseChartOptions.xaxis,
      categories: ["Total", "Published", "Homepage", "Moderation"],
    },
  });

  renderChart("#statsUsersChart", {
    ...baseChartOptions,
    chart: {
      ...baseChartOptions.chart,
      type: "bar",
      height: 330,
    },
    colors: [greenColor],
    plotOptions: {
      bar: {
        horizontal: true,
        borderRadius: 5,
        barHeight: "52%",
      },
    },
    series: [
      {
        name: "Users",
        data: [
          stats.userNum,
          stats.userMonth,
          stats.userWeek,
          stats.userDay,
          stats.userBanned,
          stats.usersOnline,
        ],
      },
    ],
    xaxis: {
      ...baseChartOptions.xaxis,
      categories: [
        "Total",
        "Month",
        "Week",
        "Day",
        "Banned",
        "Online",
      ],
    },
  });

  const commentsRaw = [
    stats.commNum,
    stats.commMonth,
    stats.commWeek,
    stats.commDay,
  ];
  const commentsMax = Math.max(
    stats.commNum,
    stats.commMonth,
    stats.commWeek,
    stats.commDay,
    1,
  );
  const commentsSeries = commentsRaw.map((value) =>
    Math.round((value / commentsMax) * 100),
  );

  renderChart("#statsCommentsChart", {
    ...baseChartOptions,
    chart: {
      ...baseChartOptions.chart,
      type: "radialBar",
      height: 330,
    },
    colors: [orangeColor, primaryColor, greenColor, violetColor],
    labels: ["Total", "Month", "Week", "Day"],
    series: commentsSeries,
    plotOptions: {
      radialBar: {
        hollow: {
          size: "38%",
        },
        track: {
          background: borderColor,
        },
        dataLabels: {
          name: {
            color: textColor,
          },
          value: {
            color: textColor,
            formatter: (value) => `${Math.round(value)}%`,
          },
          total: {
            show: true,
            label: "Total",
            color: mutedColor,
            formatter: () => String(stats.commNum),
          },
        },
      },
    },
    legend: {
      ...baseChartOptions.legend,
      show: true,
      position: "bottom",
      formatter: (seriesName, opts) =>
        `${seriesName}: ${commentsRaw[opts.seriesIndex]}`,
    },
  });
});
