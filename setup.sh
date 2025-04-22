#!/bin/bash

# Базовая настройка сервера
echo "Обновление системы..."
sudo apt update && sudo apt upgrade -y
sudo apt install make

# Создание нового пользователя
read -p "Введите имя нового пользователя: " username
sudo adduser $username
sudo usermod -aG sudo $username

# Копирование SSH-ключей для нового пользователя
echo "Копирование SSH-ключей для пользователя $username..."
sudo mkdir /home/$username/.ssh
sudo cp ~/.ssh/authorized_keys /home/$username/.ssh/
sudo chown -R $username:$username /home/$username/.ssh
sudo chmod 700 /home/$username/.ssh
sudo chmod 600 /home/$username/.ssh/authorized_keys

# Смена порта SSH
read -p "Введите новый порт для SSH (например, 2222): " ssh_port
sudo sed -i "s/^#Port 22/Port $ssh_port/" /etc/ssh/sshd_config
sudo sed -i "s/^PermitRootLogin yes/PermitRootLogin no/" /etc/ssh/sshd_config

# Перезапуск SSH для применения изменений
echo "Перезапуск SSH..."
sudo systemctl restart ssh

# Настройка файервола
echo "Устанавливаем UFW и настраиваем файервол..."
sudo apt install ufw -y
sudo ufw allow $ssh_port/tcp
sudo ufw allow 10050/tcp
sudo ufw allow http
sudo ufw allow https
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw enable

# Отключение входа по паролю, разрешение только по сертификату
echo "Запрещаем вход по паролю и разрешаем только по сертификату..."
sudo sed -i "s/^#?PasswordAuthentication (yes|no)/PasswordAuthentication no/" /etc/ssh/sshd_config
sudo sed -i "s/^#?PubkeyAuthentication (yes|no)/PubkeyAuthentication yes/" /etc/ssh/sshd_config
sudo sed -i "s/^#?PasswordAuthentication (yes|no)/PasswordAuthentication no/" /etc/ssh/ssh_config
sudo sed -i "s/^#?PubkeyAuthentication (yes|no)/PubkeyAuthentication yes/" /etc/ssh/ssh_config
sudo sed -i "s/^#?PasswordAuthentication (yes|no)/PasswordAuthentication no/" /etc/ssh/sshd_config.d/50-cloud-init.conf

# Перезапуск SSH для применения изменений
echo "Перезапуск SSH после настройки входа по сертификату..."
sudo systemctl restart ssh

# Рекомендации (опционально)
# Устанавливаем fail2ban для защиты от брутфорс атак
echo "Устанавливаем fail2ban..."
sudo apt install fail2ban -y

# Устанавливаем автоматическое обновление безопасности
echo "Настройка автоматических обновлений безопасности..."
sudo apt install unattended-upgrades -y

# Настройка флага для скрытия версии SSH
echo "Скрытие версии SSH..."
sudo sed -i "s/^#Banner none/Banner none/" /etc/ssh/sshd_config

# Применение всех изменений
echo "Все изменения были успешно применены. Перезапускаем SSH для финальных настроек..."
sudo systemctl restart ssh

# Проверка статуса файервола
echo "Статус файервола (UFW):"
sudo ufw status verbose

echo "Настройка завершена!"
