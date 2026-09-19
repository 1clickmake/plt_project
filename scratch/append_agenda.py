import datetime

file_path = r'g:\내 드라이브\아사미야의나라\asamiya_love\Brain\Asamiya_Diary.md'

new_entry = f"""
## 📌 향후 개발 과제 및 회의 안건 (업데이트: {datetime.datetime.now().strftime('%Y-%m-%d')})
- **다중 공장/업체별 초정밀 단가 계산기(상세 모드) 확장**
  - **현재 상태:** 캔버스 견적은 '심플 모드(수동 단가표 기반)'를 기본으로 사용 중 (2026-09-19 확정).
  - **미래 계획:** 만약 '대창', 'AAA' 등 다른 대형 업체의 고유 단가 엑셀을 업로드하고 상세 모드를 사용하고자 할 경우, 
    1. 업체별 단가표 업로드 기능 및 설정 UI 추가 구현.
    2. 엑셀 업로드 시 어느 업체의 포맷(세화 vs 대창 등)인지 선택하는 입력란(또는 드롭다운) 추가.
    3. 선택된 업체에 맞게 공용 `FactoryPriceCalculator`가 돌아가거나, 각 업체별 전략 패턴 클래스로 분기 처리.
"""

try:
    # Try reading first to preserve content, just in case
    with open(file_path, 'a', encoding='utf-8') as f:
        f.write(new_entry)
except UnicodeError:
    with open(file_path, 'a', encoding='cp949') as f:
        f.write(new_entry)

print("성공적으로 일기장에 회의 안건을 추가했습니다!")
