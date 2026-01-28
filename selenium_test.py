import time
import os
import traceback
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager

# Constants
BASE_URL = "http://127.0.0.1:8000"
USER_ID = "tt@gmail.com"
PASSWORD = "xxxxxx"

def run_cognisense_test():
    """
    Automates the flow from Login -> Dashboard -> Insight Streams -> Video Playback.
    """
    print("Starting Cognisense Automation Test...")
    
    
    options = webdriver.ChromeOptions()
    options.add_argument('--no-sandbox')
    options.add_argument('--disable-dev-shm-usage')
    options.add_argument('--window-size=1920,1080')
    
    driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=options)
    wait = WebDriverWait(driver, 20)
    
    try:
        # 1. Access Login Page
        print(f"Navigating to {BASE_URL}/login")
        driver.get(f"{BASE_URL}/login")
        driver.maximize_window()
        time.sleep(2)
        
        # 2. Perform Login
        print(f"Logging in as {USER_ID}...")
        email_field = wait.until(EC.presence_of_element_located((By.ID, "email")))
        pass_field = driver.find_element(By.ID, "password")
        login_btn = driver.find_element(By.CSS_SELECTOR, "button[type='submit']")
        
        email_field.send_keys(USER_ID)
        time.sleep(1)
        pass_field.send_keys(PASSWORD)
        time.sleep(1)
        login_btn.click()
        
        print("Verifying Dashboard access...")
        wait.until(EC.url_contains("/dashboard"))
        time.sleep(2)
        print(f"Dashboard loaded. Current URL: {driver.current_url}")
        
        # 4. Navigate to Insight Streams via Sidebar
        print("Navigating to Insight Streams...")
        streams_link = wait.until(EC.presence_of_element_located((By.XPATH, "//a[contains(@href, 'insight-streams')]")))
        driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", streams_link)
        time.sleep(1)
        print("Clicking Insight Streams link...")
        driver.execute_script("arguments[0].click();", streams_link)
        print("Waiting for Insight Streams page to load...")
        wait.until(EC.url_contains("/insight-streams"))
        print(f"Insight Streams loaded. Current URL: {driver.current_url}")
        time.sleep(2)
        
        # Find the first skill card's 'Open Stream' button
        print("Clicking 'Open Stream' button...")
        open_stream_btn = wait.until(EC.element_to_be_clickable((By.CSS_SELECTOR, "button[data-open-stream]")))
        driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", open_stream_btn)
        time.sleep(2)
        driver.execute_script("arguments[0].click();", open_stream_btn)
        print("Checking if stream modal opened...")
        time.sleep(3)
        driver.save_screenshot("after_click.png")
        print("Searching for Watch button...")
        watch_btn = wait.until(EC.element_to_be_clickable((By.CSS_SELECTOR, ".video-watch")))
        print("Clicking Watch button...")
        driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", watch_btn)
        time.sleep(1)
        driver.execute_script("arguments[0].click();", watch_btn)
        
        # 7. Final Verification: Video Player visibility
        print("Verifying video player...")
        video_player = wait.until(EC.presence_of_element_located((By.ID, "videoEl")))
        print("Success: Video player triggered successfully!")
        driver.save_screenshot("success.png")
        time.sleep(5)
        
        # 8. Navigate back to Dashboard to start CV generation
        print("Returning to Dashboard for CV Generation...")
        dash_link = wait.until(EC.element_to_be_clickable((By.XPATH, "//span[contains(text(), 'Dashboard')]/parent::a")))
        driver.execute_script("arguments[0].click();", dash_link)
        wait.until(EC.url_contains("/dashboard"))
        time.sleep(2)
        
        # 9. Navigate to CV Generator
        print("Navigating to CV Generator...")
        cv_link = wait.until(EC.element_to_be_clickable((By.XPATH, "//span[contains(text(), 'Generate CV')]/parent::a")))
        driver.execute_script("arguments[0].click();", cv_link)
        wait.until(EC.url_contains("/cv"))
        print(f"CV Panel loaded. Current URL: {driver.current_url}")
        time.sleep(2)
        
        # 10. Select a Template
        print("Selecting 'Modern Professional' template (or first available)...")
        use_template_btn = wait.until(EC.element_to_be_clickable((By.CSS_SELECTOR, ".cs-template:first-child .cs-btn2--primary")))
        driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", use_template_btn)
        time.sleep(1)
        driver.execute_script("arguments[0].click();", use_template_btn)
        
        # 11. Fill out the CV Form
        print("✍️ Filling out CV information...")
        wait.until(EC.url_contains("/cv/templates/"))
        time.sleep(2)
        
        # Core Profile Fields
        driver.find_element(By.NAME, "cv_title").send_keys("Professional CV")
        driver.find_element(By.NAME, "headline").send_keys("Job Search")
        driver.find_element(By.NAME, "full_name").clear()
        driver.find_element(By.NAME, "full_name").send_keys("Md Saiful Islam")
        driver.find_element(By.NAME, "location").send_keys("Dhaka, Bangladesh")
        driver.find_element(By.NAME, "email").clear()
        driver.find_element(By.NAME, "email").send_keys("mdsaifulislam@example.com")
        driver.find_element(By.NAME, "phone").send_keys("+880123456789")
        
        driver.execute_script("window.scrollTo(0, 800);")
        time.sleep(1)
        
        driver.find_element(By.NAME, "summary").send_keys("A results-driven automation expert with a passion for high-quality software delivery.")
        time.sleep(1)
        driver.find_element(By.NAME, "skills_technical").send_keys("Selenium, Python, PHP, Laravel, MySQL, Docker")
        time.sleep(1)
        driver.find_element(By.NAME, "skills_soft").send_keys("Leadership, Problem Solving, Communication")
        time.sleep(1)
        
        # Add an Experience item
        print("Adding Experience item...")
        add_exp_btn = driver.find_element(By.XPATH, "//button[contains(text(), '+ Add Experience')]")
        driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", add_exp_btn)
        time.sleep(1)
        add_exp_btn.click()
        time.sleep(1)
        
        # Fill first experience (index 0)
        driver.find_element(By.NAME, "experiences[0][role]").send_keys("Senior Developer")
        driver.find_element(By.NAME, "experiences[0][company]").send_keys("Fortechland")
        driver.find_element(By.NAME, "experiences[0][start]").send_keys("Jan 2024")
        driver.find_element(By.NAME, "experiences[0][end]").send_keys("Present")
        driver.find_element(By.NAME, "experiences[0][details]").send_keys("Automated complex user journeys with 100% reliability.\nWorking on different projects and learning new things.")
        
        time.sleep(2)
        
        # 12. Submit the CV
        print("Generating CV PDF...")
        submit_btn = driver.find_element(By.CSS_SELECTOR, "button[type='submit']")
        driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", submit_btn)
        time.sleep(2)
        driver.save_screenshot("cv_form_filled.png")
        print("Screenshot 'cv_form_filled.png' saved.")
        
        driver.execute_script("arguments[0].click();", submit_btn)
        
        # 13. Final Verification
        print("Verifying CV Generation...")
        wait.until(EC.url_to_be(f"{BASE_URL}/cv"))
        success_msg = wait.until(EC.presence_of_element_located((By.XPATH, "//*[contains(text(), 'CV generated')]")))
        print(f"Success: {success_msg.text}")
        
        driver.save_screenshot("cv_index_success.png")
        print("Screenshot 'cv_index_success.png' saved.")
        print("Opening the generated CV...")
        open_btn = wait.until(EC.element_to_be_clickable((By.XPATH, "(//div[contains(@class, 'cs-cvRow')]//a[contains(., 'Open')])[1]")))
        
        driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", open_btn)
        time.sleep(2)
        print("Clicking Open button...")
        driver.execute_script("arguments[0].click();", open_btn)
        
        # Handle new tab
        time.sleep(3)
        if len(driver.window_handles) > 1:
            driver.switch_to.window(driver.window_handles[1])
            print(f"Switched to CV View Tab. URL: {driver.current_url}")
        
        print("Final Success: CV generated and opened successfully!")
        driver.save_screenshot("cv_final_view.png")
        print("Screenshot 'cv_final_view.png' saved.")
        time.sleep(3)
        
        # 14. Skill Hub Practice Flow
        print("Starting Skill Hub Practice Flow...")
        if len(driver.window_handles) > 1:
            driver.close() # Close CV tab
            driver.switch_to.window(driver.window_handles[0])
            
        print("Returning to Dashboard...")
        try:
            # Try the "Back to Dashboard" button which exists on the CV Index page
            dash_link = wait.until(EC.element_to_be_clickable((By.XPATH, "//a[contains(text(), 'Back to Dashboard')]")))
            driver.execute_script("arguments[0].click();", dash_link)
        except:
            print("Sidebar/Back link not found, using direct URL navigation.")
            driver.get(f"{BASE_URL}/dashboard")
            
        wait.until(EC.url_contains("/dashboard"))
        
        print("Navigating to Skill Hub...")
        # On Dashboard, sidebar should exist
        skill_hub_link = wait.until(EC.element_to_be_clickable((By.XPATH, "//span[contains(text(), 'Skill Hub')]/parent::a")))
        driver.execute_script("arguments[0].click();", skill_hub_link)
        wait.until(EC.url_contains("/skill-hub"))
        time.sleep(3)
        
        print("Choosing a Skill (Interviewing)...")
        # Flip the first card (using JS to click the 'Practice' button directly or trigger card flip)
        practice_btn = wait.until(EC.presence_of_element_located((By.CSS_SELECTOR, ".skill-card:first-child .practice-btn")))
        driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", practice_btn)
        time.sleep(2)
        print("Clicking Practice on skill card...")
        driver.execute_script("arguments[0].click();", practice_btn)
        
        # Modal should appear
        print("Handling Practice Modal...")
        modal_go_btn = wait.until(EC.element_to_be_clickable((By.ID, "csPracticeGo")))
        time.sleep(1)
        driver.execute_script("arguments[0].click();", modal_go_btn)
        
        # Tips page
        print("Navigating through Tips page...")
        wait.until(EC.url_contains("/practice/tips"))
        continue_btn = wait.until(EC.element_to_be_clickable((By.XPATH, "//a[contains(text(), 'Continue')]")))
        time.sleep(2)
        driver.save_screenshot("skill_practice_tips.png")
        driver.execute_script("arguments[0].click();", continue_btn)
        
        # Scenario list
        print("Selecting a Scenario...")
        wait.until(EC.url_contains("/practice/list"))
        start_scenario_btn = wait.until(EC.element_to_be_clickable((By.CSS_SELECTOR, ".list .row:first-child .btn-primary")))
        time.sleep(2)
        driver.save_screenshot("skill_scenario_list.png")
        driver.execute_script("arguments[0].click();", start_scenario_btn)
        
        # Scenario Play page
        print("Starting Practice Session...")
        wait.until(EC.url_contains("/scenario/"))
        begin_btn = wait.until(EC.element_to_be_clickable((By.ID, "beginBtn")))
        driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", begin_btn)
        time.sleep(2)
        driver.save_screenshot("skill_practice_play.png")
        print("Clicking Begin...")
        driver.execute_script("arguments[0].click();", begin_btn)
        
        print("Success: Practice session started successfully!")
        time.sleep(5)
        driver.save_screenshot("skill_practice_started.png")
        
        # 15. Final Clean up
        print("🏁 Automation complete. Returning to Dashboard one last time.")
        dash_link_final = wait.until(EC.element_to_be_clickable((By.XPATH, "//a[contains(text(), 'Skill Hub')]"))) # Sidebar is still there
        # Let's just go home
        driver.get(f"{BASE_URL}/dashboard")
        time.sleep(2)
        print("Full automation suite passed!")
        
    except Exception as e:
        print(f"Test Failed!")
        print(f"Error Message: {str(e)}")
        print("--- Traceback ---")
        traceback.print_exc()
        print(f"Current URL: {driver.current_url}")
        
        driver.save_screenshot("test_failure.png")
        with open("page_source.html", "w") as f:
            f.write(driver.page_source)
        print("Page source and screenshot saved.")
    
    finally:
        print("Closing driver.")
        driver.quit()

if __name__ == "__main__":
    run_cognisense_test()
