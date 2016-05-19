# bbb-mp4
BigBlueButton Record Process

# �������

## extractCursorEvents
    php extractCursorEvents.php --src=events.xml --dst=events.new.xml > cursor.events

��������� ��������� ������� �� ����� events.xml. ������� ����� ���� events.new.xml ��� ���. ��������� �� �������
��������� � stdout ��� CSV "timestamp,x,y"

## generateCursorPng
    php generateCursorPng.php --src=./cursor.events --dst=./cursor/ --width=1280 --height=720 --diameter=10

������� �� ���� ����� ������� ������� � ������� CSV ������������������ ������ � ������� PNG � ������������ �������.
�������� �����, ���� ����� ��������� ������������������ �����������, �� ������ � ������ � ������ ����� �������

## extractVoiceEvents
    php extractVoiceEvents.php --src=events.xml > voice.events

��������� ��������� ������� �� ����� events.xml � ������� �� � stdout ��� CSV "Start/Stop,timestamp,filename".

## makeSound
    php makeSound.php --src=voice.events --src-dir=./audio --dst=sound.wav

������� �� ���� ����� ��������� ������� � ���������� ���� ������� �������� ����. �������� ����, ���� �����
��������� ��������� � ����������� ���� � �����, ��� ����������� ���������. � ����� ��������� ����� �����
��������� ������� ������� ������ ������� ���������, ��������: 2419160065.sound.wav.

## makeLayout
    php makeLayout.php --width=1280 --height=720 --dst=test.png > contents.coords

������� ����������� ���� �� ���� ��������. �������� ������ � ������ ���� (�����������), ���� � �����, � �������
����� ��������� �����������. ���������� �������� ��������� � stdout ��� CSV "�������� ����,x,y,width,height".

## makeDeskshareLayout
    php php makeDeskshareLayout.php --src=content.coords --dst=deskshare.png

������� ����������� ���� ��� ���� ���������� �������� ����� �� ���� ���������, ���������� � ����������
���������� makeLayout. �������� ���� � ���������� ������ makeLayout � ���� � �����, � ������� ����� ���������
�����������.

# ���� (��� �� ������� ������)
## NotesWindow
## BroadcastWindow
## PresentationWindow
���� ������ �������
## VideoDock
## ChatWindow
���� ����
## UsersWindow
������ �������������
## ViewersWindow
## ListenersWindow