        // Holidays
        var showDates = document.getElementsByClassName("calendar-dates")[0].children[0].children;
        var reference;
        if (parseInt($('.month_dropdown').val()) === 1) {
          for (let i = 0; i < showDates.length; i++) {
            checkDates = showDates[i].attributes;
            if ('data-date' in checkDates) {
              if (showDates[i].attributes[0].textContent.includes('-01-01')) {
                const newyeDay = document.createElement('span');
                newyeDay.classList.add("holiday");
                newyeDay.append("New Year's Day");
                showDates[i].append(newyeDay);
              }
            }
          }
          let thirdMon;
          if ('data-date' in showDates[1].attributes) {
            reference = 15;
            thirdMon = showDates[reference].attributes[0].textContent;
          } else {
            reference = 22;
            thirdMon = showDates[reference].attributes[0].textContent;
          }
          const thirdMonDay = thirdMon.substr(8);
          const mlkjrDate = '-01-' + thirdMonDay;
          if (showDates[reference].attributes[0].textContent.includes(mlkjrDate)) {
            const mlkjrDay = document.createElement('span');
            mlkjrDay.classList.add("holiday");
            mlkjrDay.append("Martin Luther King Jr. Day");
            showDates[reference].append(mlkjrDay);
          }
        }
        else if (parseInt($('.month_dropdown').val()) === 2) {
          let thirdMon;
          if ('data-date' in showDates[1].attributes) {
            reference = 15;
            thirdMon = showDates[reference].attributes[0].textContent;
          } else {
            reference = 22;
            thirdMon = showDates[reference].attributes[0].textContent;
          }
          const thirdMonDay = thirdMon.substr(8);
          const presiDate = '-02-' + thirdMonDay;
          if (showDates[reference].attributes[0].textContent.includes(presiDate)) {
            const presiDay = document.createElement('span');
            presiDay.classList.add("holiday");
            presiDay.append("Presidents' Day");
            showDates[reference].append(presiDay);
          }
          for (let i = 0; i < showDates.length; i++) {
            checkDates = showDates[i].attributes;
            if ('data-date' in checkDates) {
              if (showDates[i].attributes[0].textContent.includes('-02-02')) {
                const grounDay = document.createElement('span');
                grounDay.classList.add("holiday");
                grounDay.append("Groundhog Day");
                showDates[i].append(grounDay);
              }
              if (showDates[i].attributes[0].textContent.includes('-02-14')) {
                const valenDay = document.createElement('span');
                valenDay.classList.add("holiday");
                valenDay.append("Valentine's Day");
                showDates[i].append(valenDay);
              }
            }
          }
        }
        else if (parseInt($('.month_dropdown').val()) === 3) {
          for (let i = 0; i < showDates.length; i++) {
            checkDates = showDates[i].attributes;
            if ('data-date' in checkDates) {
              if (showDates[i].attributes[0].textContent.includes('-03-17')) {
                const stpatDay = document.createElement('span');
                stpatDay.classList.add("holiday");
                stpatDay.append("St. Patrick's Day");
                showDates[i].append(stpatDay);
              }
            }
          }
        }
        else if (parseInt($('.month_dropdown').val()) === 4) {
          for (let i = 0; i < showDates.length; i++) {
            checkDates = showDates[i].attributes;
            if ('data-date' in checkDates) {
              if (showDates[i].attributes[0].textContent.includes('-04-01')) {
                const aprilDay = document.createElement('span');
                aprilDay.classList.add("holiday");
                aprilDay.append("April Fools' Day");
                showDates[i].append(aprilDay);
              }
              if (showDates[i].attributes[0].textContent.includes('-04-22')) {
                const earthDay = document.createElement('span');
                earthDay.classList.add("holiday");
                earthDay.append("Earth Day");
                showDates[i].append(earthDay);
              }
            }
          }
        }
        else if (parseInt($('.month_dropdown').val()) === 5) {
          let lastMon;
          if (showDates[36]) {
            if ('data-date' in showDates[36].attributes) {
              reference = 36;
              lastMon = showDates[reference].attributes[0].textContent;
            } else {
              reference = 29;
              lastMon = showDates[reference].attributes[0].textContent;
            }
          } else {
            if ('data-date' in showDates[29].attributes) {
              reference = 29;
              lastMon = showDates[reference].attributes[0].textContent;
            } else {
              reference = 22;
              lastMon = showDates[reference].attributes[0].textContent;
            }
          }
          const lastMonDay = lastMon.substr(8);
          const memorDate = '-05-' + lastMonDay;
          if (showDates[reference].attributes[0].textContent.includes(memorDate)) {
            const memorDay = document.createElement('span');
            memorDay.classList.add("holiday");
            memorDay.append("Memorial Day");
            showDates[reference].append(memorDay);
          }
          let reference2;
          let secondSun;
          if ('data-date' in showDates[0].attributes) {
            reference2 = 7;
            secondSun = showDates[reference2].attributes[0].textContent;
          } else {
            reference2 = 14;
            secondSun = showDates[reference2].attributes[0].textContent;
          }
          const secondSunDay = secondSun.substr(8);
          const motheDate = '-05-' + secondSunDay;
          if (showDates[reference2].attributes[0].textContent.includes(motheDate)) {
            const motheDay = document.createElement('span');
            motheDay.classList.add("holiday");
            motheDay.append("Mothers' Day");
            showDates[reference2].append(motheDay);
          }
          for (let i = 0; i < showDates.length; i++) {
            checkDates = showDates[i].attributes;
            if ('data-date' in checkDates) {
              if (showDates[i].attributes[0].textContent.includes('-05-05')) {
                const cincoDay = document.createElement('span');
                cincoDay.classList.add("holiday");
                cincoDay.append("Cinco de Mayo");
                showDates[i].append(cincoDay);
              }
            }
          }
        }
        else if (parseInt($('.month_dropdown').val()) === 6) {
          let thirdSun;
          if ('data-date' in showDates[0].attributes) {
            reference = 14;
            thirdSun = showDates[reference].attributes[0].textContent;
          } else {
            reference = 21;
            thirdSun = showDates[reference].attributes[0].textContent;
          }
          const thirdSunDay = thirdSun.substr(8);
          const fatheDate = '-06-' + thirdSunDay;
          if (showDates[reference].attributes[0].textContent.includes(fatheDate)) {
            const fatheDay = document.createElement('span');
            fatheDay.classList.add("holiday");
            fatheDay.append("Fathers' Day");
            showDates[reference].append(fatheDay);
          }
        }
        else if (parseInt($('.month_dropdown').val()) === 7) {
          for (let i = 0; i < showDates.length; i++) {
            checkDates = showDates[i].attributes;
            if ('data-date' in checkDates) {
              if (showDates[i].attributes[0].textContent.includes('-07-04')) {
                const indepDay = document.createElement('span');
                indepDay.classList.add("holiday");
                indepDay.append("Independence Day");
                showDates[i].append(indepDay);
              }
            }
          }
        }
        else if (parseInt($('.month_dropdown').val()) === 8) {
        }
        else if (parseInt($('.month_dropdown').val()) === 9) {
          let firstMon;
          if ('data-date' in showDates[1].attributes) {
            reference = 1;
            firstMon = showDates[reference].attributes[0].textContent;
          } else {
            reference = 8;
            firstMon = showDates[reference].attributes[0].textContent;
          }
          const firstMonDay = firstMon.substr(8);
          const laborDate = '-09-' + firstMonDay;
          if (showDates[reference].attributes[0].textContent.includes(laborDate)) {
            const laborDay = document.createElement('span');
            laborDay.classList.add("holiday");
            laborDay.append("Labor Day");
            showDates[reference].append(laborDay);
          }
        }
        else if (parseInt($('.month_dropdown').val()) === 10) {
          let secondMon;
          if ('data-date' in showDates[1].attributes) {
            reference = 8;
            secondMon = showDates[reference].attributes[0].textContent;
          } else {
            reference = 15;
            secondMon = showDates[reference].attributes[0].textContent;
          }
          const secondMonDay = secondMon.substr(8);
          const columDate = '-10-' + secondMonDay;
          if (showDates[reference].attributes[0].textContent.includes(columDate)) {
            const columDay = document.createElement('span');
            columDay.classList.add("holiday");
            columDay.append("Columbus Day");
            showDates[reference].append(columDay);
          }
          for (let i = 0; i < showDates.length; i++) {
            checkDates = showDates[i].attributes;
            if ('data-date' in checkDates) {
              if (showDates[i].attributes[0].textContent.includes('-10-31')) {
                const hallowee = document.createElement('span');
                hallowee.classList.add("holiday");
                hallowee.append("Halloween");
                showDates[i].append(hallowee);
              }
            }
          }
        }
        else if (parseInt($('.month_dropdown').val()) === 11) {
          for (let i = 0; i < showDates.length; i++) {
            checkDates = showDates[i].attributes;
            if ('data-date' in checkDates) {
              if (showDates[i].attributes[0].textContent.includes('-11-11')) {
                const veterDay = document.createElement('span');
                veterDay.classList.add("holiday");
                veterDay.append("Veterans Day");
                showDates[i].append(veterDay);
              }
            }
          }
          let fourthThu;
          if ('data-date' in showDates[4].attributes) {
            reference = 25;
            fourthThu = showDates[reference].attributes[0].textContent;
          } else {
            reference = 32;
            fourthThu = showDates[reference].attributes[0].textContent;
          }
          const fourthThuDay = fourthThu.substr(8);
          const thankDate = '-11-' + fourthThuDay;
          if (showDates[reference].attributes[0].textContent.includes(thankDate)) {
            const thankDay = document.createElement('span');
            thankDay.classList.add("holiday");
            thankDay.append("Thanksgiving Day");
            showDates[reference].append(thankDay);
          }
        }
        else {
          for (let i = 0; i < showDates.length; i++) {
            checkDates = showDates[i].attributes;
            if ('data-date' in checkDates) {
              if (showDates[i].attributes[0].textContent.includes('-12-24')) {
                const chrisEve = document.createElement('span');
                chrisEve.classList.add("holiday");
                chrisEve.append("Christmas Eve");
                showDates[i].append(chrisEve);
              }
              if (showDates[i].attributes[0].textContent.includes('-12-25')) {
                const chrisDay = document.createElement('span');
                chrisDay.classList.add("holiday");
                chrisDay.append("Christmas Day");
                showDates[i].append(chrisDay);
              }
              if (showDates[i].attributes[0].textContent.includes('-12-31')) {
                const newyeEve = document.createElement('span');
                newyeEve.classList.add("holiday");
                newyeEve.append("New Year's Eve");
                showDates[i].append(newyeEve);
              }
            }
          }
        }
