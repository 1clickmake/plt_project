import os
import time
import random
import smtplib
import threading
import tkinter as tk
from tkinter import filedialog, messagebox
import customtkinter as ctk
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
from email.utils import formataddr
from openpyxl import load_workbook, Workbook
from dotenv import load_dotenv, set_key

# 기본 설정 (다크모드 및 색상 테마)
ctk.set_appearance_mode("Dark")  
ctk.set_default_color_theme("blue")  

ENV_FILE = ".env"
EXCEL_FILE = "contacts.xlsx"

# 초기 로드
load_dotenv(ENV_FILE)

class LuxuryPromoMailerApp(ctk.CTk):
    def __init__(self):
        super().__init__()
        
        self.title("Universal Promo Mailer (Luxury Edition 💎)")
        self.geometry("750x800")
        
        # 자가 증식 아키텍처 실행
        self.ensure_files_exist()
        
        self.excel_path = tk.StringVar(value=EXCEL_FILE)
        
        self.create_widgets()
        
    def ensure_files_exist(self):
        # 1. 엑셀 파일이 없으면 자동 생성
        if not os.path.exists(EXCEL_FILE):
            wb = Workbook()
            ws = wb.active
            ws.append(['회사명', '이름', '이메일', '발송상태', '열어봄', '답장', '실패사유'])
            ws.append(['(주)어썸컴퍼니', '김대표', 'example@gmail.com', '', '', '', ''])
            wb.save(EXCEL_FILE)
            
        # 2. .env 파일이 없으면 껍데기 자동 생성
        if not os.path.exists(ENV_FILE):
            with open(ENV_FILE, "w", encoding="utf-8") as f:
                f.write("SMTP_SERVER=smtp.gmail.com\nSMTP_PORT=465\nSMTP_USER=\nSMTP_PASS=\nSENDER_NAME=홍보담당자\n")
        
    def create_widgets(self):
        # 메인 프레임
        self.main_frame = ctk.CTkFrame(self)
        self.main_frame.pack(fill=tk.BOTH, expand=True, padx=20, pady=20)
        
        # 1. 헤더 (타이틀 & 설정)
        header_frame = ctk.CTkFrame(self.main_frame, fg_color="transparent")
        header_frame.pack(fill=tk.X, pady=(0, 20))
        
        title_lbl = ctk.CTkLabel(header_frame, text="🚀 스마트 만능 메일 발송기", font=ctk.CTkFont(size=20, weight="bold"))
        title_lbl.pack(side=tk.LEFT)
        
        btn_settings = ctk.CTkButton(header_frame, text="⚙️ 환경 설정", command=self.open_settings, width=120)
        btn_settings.pack(side=tk.RIGHT)
        
        # 2. 엑셀 파일 선택
        file_frame = ctk.CTkFrame(self.main_frame)
        file_frame.pack(fill=tk.X, pady=10)
        
        ctk.CTkLabel(file_frame, text="📂 주소록 (Excel):").pack(side=tk.LEFT, padx=10, pady=10)
        self.ent_file = ctk.CTkEntry(file_frame, textvariable=self.excel_path, state="readonly", width=400)
        self.ent_file.pack(side=tk.LEFT, padx=10)
        
        btn_browse = ctk.CTkButton(file_frame, text="찾아보기...", command=self.browse_file, width=100)
        btn_browse.pack(side=tk.LEFT, padx=(0, 10))
        
        # 3. 메일 내용 입력
        content_frame = ctk.CTkFrame(self.main_frame)
        content_frame.pack(fill=tk.BOTH, expand=True, pady=10)
        
        ctk.CTkLabel(content_frame, text="제목:").pack(anchor=tk.W, padx=10, pady=(10, 0))
        self.ent_subject = ctk.CTkEntry(content_frame, width=650)
        self.ent_subject.pack(fill=tk.X, padx=10, pady=5)
        self.ent_subject.insert(0, "[{{회사명}}] {{이름}} 대표님, 안녕하세요!")
        
        ctk.CTkLabel(content_frame, text="본문 (HTML 지원):").pack(anchor=tk.W, padx=10, pady=(10, 0))
        self.txt_body = ctk.CTkTextbox(content_frame, height=200, width=650)
        self.txt_body.pack(fill=tk.BOTH, expand=True, padx=10, pady=(5, 10))
        
        # 기본 템플릿 삽입
        default_html = (
            "<h1>안녕하세요 {{회사명}} {{이름}} 대표님!</h1>\n"
            "<p>저희의 놀라운 서비스를 지금 확인해보세요.</p>\n"
            "<p>수신거부: <a href='#'>여기</a>를 클릭하세요.</p>"
        )
        self.txt_body.insert(tk.END, default_html)
            
        # 4. 발송 버튼
        self.btn_send = ctk.CTkButton(self.main_frame, text="🔥 발송 시작!", font=ctk.CTkFont(size=18, weight="bold"), height=50, fg_color="#10b981", hover_color="#059669", command=self.start_sending_thread)
        self.btn_send.pack(fill=tk.X, pady=15)
        
        # 5. 로그 콘솔
        self.txt_log = ctk.CTkTextbox(self.main_frame, height=150, state=tk.DISABLED, fg_color="#1f2937", text_color="#34d399")
        self.txt_log.pack(fill=tk.BOTH, expand=True)
        
    def log(self, message):
        self.txt_log.configure(state=tk.NORMAL)
        self.txt_log.insert(tk.END, f"[{time.strftime('%H:%M:%S')}] {message}\n")
        self.txt_log.see(tk.END)
        self.txt_log.configure(state=tk.DISABLED)
        self.update_idletasks()

    def browse_file(self):
        filepath = filedialog.askopenfilename(filetypes=[("Excel Files", "*.xlsx")])
        if filepath:
            self.excel_path.set(filepath)

    def open_settings(self):
        # 모달 창 대신 새 창 띄우기 (CustomTkinter Toplevel)
        win = ctk.CTkToplevel(self)
        win.title("환경 설정")
        win.geometry("400x350")
        win.grab_set() # 포커스 잡기 (Modal)
        
        ctk.CTkLabel(win, text="구글 이메일 (발송용):").pack(anchor=tk.W, padx=20, pady=(20, 0))
        ent_email = ctk.CTkEntry(win, width=360)
        ent_email.pack(padx=20, pady=5)
        ent_email.insert(0, os.getenv("SMTP_USER", ""))
        
        ctk.CTkLabel(win, text="앱 비밀번호 (16자리):").pack(anchor=tk.W, padx=20, pady=(10, 0))
        ent_pass = ctk.CTkEntry(win, width=360, show="*")
        ent_pass.pack(padx=20, pady=5)
        ent_pass.insert(0, os.getenv("SMTP_PASS", ""))
        
        ctk.CTkLabel(win, text="보내는 사람 이름:").pack(anchor=tk.W, padx=20, pady=(10, 0))
        ent_name = ctk.CTkEntry(win, width=360)
        ent_name.pack(padx=20, pady=5)
        ent_name.insert(0, os.getenv("SENDER_NAME", "홍보담당자"))
        
        def save():
            set_key(ENV_FILE, "SMTP_SERVER", "smtp.gmail.com")
            set_key(ENV_FILE, "SMTP_PORT", "465")
            set_key(ENV_FILE, "SMTP_USER", ent_email.get())
            set_key(ENV_FILE, "SMTP_PASS", ent_pass.get())
            set_key(ENV_FILE, "SENDER_NAME", ent_name.get())
            set_key(ENV_FILE, "SENDER_EMAIL", ent_email.get())
            load_dotenv(ENV_FILE, override=True)
            messagebox.showinfo("저장 완료", "설정이 럭셔리하게 저장되었습니다!", parent=win)
            win.destroy()
            
        ctk.CTkButton(win, text="💾 저장하기", command=save, fg_color="#3b82f6", hover_color="#2563eb", height=40).pack(pady=30, padx=20, fill=tk.X)
        
    def start_sending_thread(self):
        if not os.getenv("SMTP_USER") or not os.getenv("SMTP_PASS"):
            messagebox.showerror("오류", "먼저 [환경 설정]에서 이메일과 앱 비밀번호를 셋팅해주세요!")
            return
            
        excel_file = self.excel_path.get()
        if not os.path.exists(excel_file):
            messagebox.showerror("오류", f"엑셀 파일이 없습니다: {excel_file}")
            return
            
        self.btn_send.configure(state=tk.DISABLED, text="🚀 발송 엔진 가동 중... (잠시만요!)", fg_color="#6b7280")
        
        # 백그라운드 스레드
        threading.Thread(target=self.run_mailer, daemon=True).start()

    def send_email_with_retry(self, to_email, subject, body_html):
        sender_name = os.getenv("SENDER_NAME")
        sender_email = os.getenv("SMTP_USER")
        
        msg = MIMEMultipart("alternative")
        msg["Subject"] = subject
        msg["From"] = formataddr((sender_name, sender_email))
        msg["To"] = to_email
        msg.attach(MIMEText(body_html, "html"))

        last_error = ""
        for attempt in range(1, 4):
            try:
                with smtplib.SMTP_SSL("smtp.gmail.com", 465) as server:
                    server.login(sender_email, os.getenv("SMTP_PASS"))
                    server.sendmail(sender_email, to_email, msg.as_string())
                return True, ""
            except Exception as e:
                last_error = str(e)
                self.log(f"⚠️ 발송 실패 (시도 {attempt}/3) - {to_email}: {last_error}")
                time.sleep(3)
        return False, last_error

    def run_mailer(self):
        try:
            self.log("💎 럭셔리 발송 엔진을 시작합니다...")
            wb = load_workbook(self.excel_path.get())
            ws = wb.active
            
            headers = {cell.value: idx for idx, cell in enumerate(ws[1])}
            
            if "이메일" not in headers or "발송상태" not in headers:
                self.log("❌ 오류: 엑셀 파일 첫 줄에 '이메일' 및 '발송상태' 열이 필수입니다.")
                self.btn_send.configure(state=tk.NORMAL, text="🔥 발송 시작!", fg_color="#10b981")
                return
                
            # 실패사유 열이 없으면 맨 끝에 자동 추가
            if "실패사유" not in headers:
                new_col = ws.max_column + 1
                ws.cell(row=1, column=new_col).value = "실패사유"
                headers["실패사유"] = new_col - 1

            idx_email = headers["이메일"]
            idx_status = headers["발송상태"]
            idx_company = headers.get("회사명", -1)
            idx_name = headers.get("이름", -1)
            idx_reason = headers["실패사유"]
            
            total_rows = ws.max_row - 1
            success_count = 0
            fail_count = 0
            skipped_count = 0

            subject_template = self.ent_subject.get()
            body_template = self.txt_body.get("1.0", tk.END)

            for row_num, row in enumerate(ws.iter_rows(min_row=2), start=2):
                email = row[idx_email].value
                status = row[idx_status].value
                
                if status == "발송완료":
                    skipped_count += 1
                    continue
                    
                if not email or "@" not in email:
                    skipped_count += 1
                    continue

                company = row[idx_company].value if idx_company != -1 else "고객"
                name = row[idx_name].value if idx_name != -1 else "대표"
                if not company: company = "고객"
                if not name: name = "대표"

                subject = subject_template.replace("{{회사명}}", company).replace("{{이름}}", name)
                body = body_template.replace("{{회사명}}", company).replace("{{이름}}", name)

                self.log(f"✉️ 발송 중: {company} {name}님 ({email})")
                
                is_success, error_msg = self.send_email_with_retry(email, subject, body)
                
                if is_success:
                    ws.cell(row=row_num, column=idx_status+1).value = "발송완료"
                    ws.cell(row=row_num, column=idx_reason+1).value = "" # 에러 지우기
                    self.log(f"✅ 발송 성공: {email}")
                    success_count += 1
                else:
                    ws.cell(row=row_num, column=idx_status+1).value = "발송실패"
                    ws.cell(row=row_num, column=idx_reason+1).value = error_msg # 에러 사유 기록
                    self.log(f"❌ 최종 발송 실패: {email} (이유: {error_msg})")
                    fail_count += 1
                    
                try:
                    wb.save(self.excel_path.get())
                except PermissionError:
                    self.log("⚠️ 경고: 엑셀 파일이 현재 열려 있어서 결과를 저장할 수 없습니다! 엑셀 창을 닫아주세요.")

                delay = random.uniform(3, 5)
                self.log(f"⏳ 스팸 차단 방어: {delay:.1f}초 대기 중...")
                time.sleep(delay)

            self.log(f"🎉 럭셔리 발송 작업이 모두 완료되었습니다!")
            
            # 최종 팝업 보고
            summary = (
                f"총 연락처: {total_rows}건\n"
                f"✅ 성공: {success_count}건\n"
                f"❌ 실패: {fail_count}건\n"
                f"⏭️ 건너뜀(이상한 메일/이미 발송): {skipped_count}건\n\n"
                f"⚠️ 실패 사유는 엑셀 파일의 '실패사유' 열에 자세히 기록되었습니다!"
            )
            messagebox.showinfo("완료 보고서", summary)

        except Exception as e:
            self.log(f"❌ 치명적 오류 발생: {e}")
            messagebox.showerror("오류", f"프로그램 실행 중 오류가 발생했습니다.\n{e}")
            
        finally:
            self.btn_send.configure(state=tk.NORMAL, text="🔥 발송 시작!", fg_color="#10b981")

if __name__ == "__main__":
    app = LuxuryPromoMailerApp()
    app.mainloop()
